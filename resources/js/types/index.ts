export type TableHeader = {
    title: string;
    key: string;
};

export type Crumb = {
    title: string;
    disabled: boolean;
};

export type TImageWithText = {
    reverse?: boolean;
    image: string;
    title: string;
    subtitle: string;
};

export type TNav = {
    title: string;
    link: string;
    icon: string;
};

export type TSideNav = {
    navs: TNav[];
};
