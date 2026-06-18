export function  decodeEntities(str : string) {
    const txt = document.createElement("textarea");
    txt.innerHTML = str;
    return txt.value;
}

export const formatForDatetimeLocal = (dateString?: string | null) => {
    if (!dateString) return null;

    const date = new Date(dateString);

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

export const formatForDisplay = (dateString?: string | null) => {
    const localDateTime = formatForDatetimeLocal(dateString);
    return localDateTime ? localDateTime.replace('T', ' ') : null;
};
