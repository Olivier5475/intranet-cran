import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Api\DocumentController::uploadImage
 * @see app/Http/Controllers/Api/DocumentController.php:8
 * @route '/api/api/documents/upload-image'
 */
export const uploadImage = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadImage.url(options),
    method: 'post',
})

uploadImage.definition = {
    methods: ["post"],
    url: '/api/api/documents/upload-image',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\DocumentController::uploadImage
 * @see app/Http/Controllers/Api/DocumentController.php:8
 * @route '/api/api/documents/upload-image'
 */
uploadImage.url = (options?: RouteQueryOptions) => {
    return uploadImage.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DocumentController::uploadImage
 * @see app/Http/Controllers/Api/DocumentController.php:8
 * @route '/api/api/documents/upload-image'
 */
uploadImage.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadImage.url(options),
    method: 'post',
})
const document = {
    uploadImage: Object.assign(uploadImage, uploadImage),
}

export default document