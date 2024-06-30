<?php

namespace App\Services;

use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Configuration;
use Lcobucci\Clock\SystemClock;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\JwtToken;

class JwtService
{
    private $config;

    public function __construct()
    {
        $this->config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(config('jwt.keys.private')),
            InMemory::file(config('jwt.keys.public'))
        );
    }

    public function generateToken(User $user)
    {
        $now = new \DateTimeImmutable();
        $expiresAt = $now->modify('+1 hour');

        $uniqueId = (string) Str::uuid();

        $token = $this->config->builder()
            ->issuedBy(config('jwt.issuer'))
            ->issuedAt($now)
            ->expiresAt($expiresAt)
            ->withClaim('user_uuid', $user->uuid)
            ->withClaim('unique_id', $uniqueId)
            ->getToken($this->config->signer(), $this->config->signingKey());

        // Store the token metadata in the database
        JwtToken::create([
            'user_id' => $user->id,
            'unique_id' => $uniqueId,
            'token_title' => 'User Auth Token',
            'permissions' => json_encode(['*']),
            'restrictions' => null,
            'expires_at' => $expiresAt,
            'last_used_at' => now(),
            'refreshed_at' => now(),
        ]);

        return $token->toString();
    }

    public function invalidateToken(User $user)
    {
        // Get the current token for the user
        $currentToken = JwtToken::where('user_id', $user->id)
                                ->latest('created_at')
                                ->first();

        if ($currentToken) {
            // Set the token's expiration to the current time, effectively invalidating it
            $currentToken->expires_at = Carbon::now();
            $currentToken->save();
        }
    }

    public function validateToken($token)
    {
        try {
            $jwt = $this->config->parser()->parse($token);

            // Define the constraints for validation
            $constraints = [
                new SignedWith($this->config->signer(), $this->config->verificationKey()),
                new ValidAt(SystemClock::fromUTC())
            ];

            $uniqueId = $jwt->claims()->get('unique_id');

            // Check if token is in the database and not expired
            $storedToken = JwtToken::where('unique_id', $uniqueId)->first();
            if (!$storedToken || Carbon::now()->greaterThan($storedToken->expires_at)) {
                return false;
            }

            $this->config->validator()->assert($jwt, ...$constraints);
            return true;
        } catch (RequiredConstraintsViolated $e) {
            return false;
        }
    }

    public function getUserUuidFromToken($token)
    {
        $token = $this->config->parser()->parse($token);
        assert($token instanceof Plain);

        return $token->claims()->get('user_uuid');
    }
}
