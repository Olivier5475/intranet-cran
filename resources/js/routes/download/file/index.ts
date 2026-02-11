import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\DownloadController::version
 * @see app/Http/Controllers/DownloadController.php:32
 * @route '/download/version/{id}'
 */
export const version = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: version.url(args, options),
    method: 'get',
})

version.definition = {
    methods: ["get","head"],
    url: '/download/version/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DownloadController::version
 * @see app/Http/Controllers/DownloadController.php:32
 * @route '/download/version/{id}'
 */
version.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return version.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DownloadController::version
 * @see app/Http/Controllers/DownloadController.php:32
 * @route '/download/version/{id}'
 */
version.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: version.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DownloadController::version
 * @see app/Http/Controllers/DownloadController.php:32
 * @route '/download/version/{id}'
 */
version.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: version.url(args, options),
    method: 'head',
})
const file = {
    version: Object.assign(version, version),
}

export default file