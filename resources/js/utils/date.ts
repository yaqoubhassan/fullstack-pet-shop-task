import dayjs from "dayjs";

export const formatDate = (
    date: string | Date,
    format = "YYYY-MM-DD"
): string => {
    return dayjs(date).format(format);
};
