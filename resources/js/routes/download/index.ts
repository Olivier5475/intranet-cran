import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
import fileF7f338 from './file'
/**
* @see \App\Http\Controllers\DownloadController::attachment
 * @see app/Http/Controllers/DownloadController.php:16
 * @route '/download/attachment/{id}'
 */
export const attachment = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: attachment.url(args, options),
    method: 'get',
})

attachment.definition = {
    methods: ["get","head"],
    url: '/download/attachment/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DownloadController::attachment
 * @see app/Http/Controllers/DownloadController.php:16
 * @route '/download/attachment/{id}'
 */
attachment.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return attachment.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DownloadController::attachment
 * @see app/Http/Controllers/DownloadController.php:16
 * @route '/download/attachment/{id}'
 */
attachment.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: attachment.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DownloadController::attachment
 * @see app/Http/Controllers/DownloadController.php:16
 * @route '/download/attachment/{id}'
 */
attachment.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: attachment.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DownloadController::file
 * @see app/Http/Controllers/DownloadController.php:28
 * @route '/download/file/{id}'
 */
export const file = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: file.url(args, options),
    method: 'get',
})

file.definition = {
    methods: ["get","head"],
    url: '/download/file/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DownloadController::file
 * @see app/Http/Controllers/DownloadController.php:28
 * @route '/download/file/{id}'
 */
file.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return file.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DownloadController::file
 * @see app/Http/Controllers/DownloadController.php:28
 * @route '/download/file/{id}'
 */
file.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: file.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DownloadController::file
 * @see app/Http/Controllers/DownloadController.php:28
 * @route '/download/file/{id}'
 */
file.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: file.url(args, options),
    method: 'head',
})
const download = {
    attachment: Object.assign(attachment, attachment),
file: Object.assign(file, fileF7f338),
}

export default download