import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\NavigationController::__invoke
 * @see app/Http/Controllers/NavigationController.php:17
 * @route '/navigation/f/{folder_id}'
 */
export const folder = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: folder.url(args, options),
    method: 'get',
})

folder.definition = {
    methods: ["get","head"],
    url: '/navigation/f/{folder_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\NavigationController::__invoke
 * @see app/Http/Controllers/NavigationController.php:17
 * @route '/navigation/f/{folder_id}'
 */
folder.url = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { folder_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    folder_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        folder_id: args.folder_id,
                }

    return folder.definition.url
            .replace('{folder_id}', parsedArgs.folder_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\NavigationController::__invoke
 * @see app/Http/Controllers/NavigationController.php:17
 * @route '/navigation/f/{folder_id}'
 */
folder.get = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: folder.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\NavigationController::__invoke
 * @see app/Http/Controllers/NavigationController.php:17
 * @route '/navigation/f/{folder_id}'
 */
folder.head = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: folder.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DocumentViewController::__invoke
 * @see app/Http/Controllers/DocumentViewController.php:14
 * @route '/navigation/d/{document_id}'
 */
export const document = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: document.url(args, options),
    method: 'get',
})

document.definition = {
    methods: ["get","head"],
    url: '/navigation/d/{document_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DocumentViewController::__invoke
 * @see app/Http/Controllers/DocumentViewController.php:14
 * @route '/navigation/d/{document_id}'
 */
document.url = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { document_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    document_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        document_id: args.document_id,
                }

    return document.definition.url
            .replace('{document_id}', parsedArgs.document_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DocumentViewController::__invoke
 * @see app/Http/Controllers/DocumentViewController.php:14
 * @route '/navigation/d/{document_id}'
 */
document.get = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: document.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DocumentViewController::__invoke
 * @see app/Http/Controllers/DocumentViewController.php:14
 * @route '/navigation/d/{document_id}'
 */
document.head = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: document.url(args, options),
    method: 'head',
})
const navigate = {
    folder: Object.assign(folder, folder),
document: Object.assign(document, document),
}

export default navigate