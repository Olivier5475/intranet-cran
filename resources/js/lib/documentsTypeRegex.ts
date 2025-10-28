export const isPresentationFile = (file : string) => {
    const extensionRegex = /.(pptx|ppt|ppsx|pps|odp|otp|pptm|potx)$/i ;
    return extensionRegex.test(file) ;
};

export const isDocFile = (file : string) => {
    const extensionRegex = /\.(doc([xm])?|dot([xm])?|o(dt|tt)|rtf)$/i ;
    return extensionRegex.test(file) ;
};

export const isTabFile = (file : string) => {
    const extensionRegex = /\.(xl(s[xmb]?|t[xm]?)|o[dt]s|[ct]sv)$/i ;
    return extensionRegex.test(file) ;
};

export const isVideoFile = (file : string) => {
    const extensionRegex = /\.(mp4|mov|avi|mkv|wmv|flv|webm|mpeg|mpg|3gp|m4v)$/i ;
    return extensionRegex.test(file) ;
};

export const isImageFile = (file : string) => {
    const extensionRegex = /\.(jpg|jpeg|png|gif|bmp|svg|webp|avif|ico|tiff|heic|heif)$/i ;
    return extensionRegex.test(file) ;
};
