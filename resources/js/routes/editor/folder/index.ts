import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
import post from './post'
/**
* @see \App\Http\Controllers\Admin\FolderController::create
 * @see app/Http/Controllers/Admin/FolderController.php:19
 * @route '/editor/folders/create/p/{parent_id}'
 */
export const create = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(args, options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/editor/folders/create/p/{parent_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\FolderController::create
 * @see app/Http/Controllers/Admin/FolderController.php:19
 * @route '/editor/folders/create/p/{parent_id}'
 */
create.url = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { parent_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    parent_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        parent_id: args.parent_id,
                }

    return create.definition.url
            .replace('{parent_id}', parsedArgs.parent_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\FolderController::create
 * @see app/Http/Controllers/Admin/FolderController.php:19
 * @route '/editor/folders/create/p/{parent_id}'
 */
create.get = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\FolderController::create
 * @see app/Http/Controllers/Admin/FolderController.php:19
 * @route '/editor/folders/create/p/{parent_id}'
 */
create.head = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\FolderController::update
 * @see app/Http/Controllers/Admin/FolderController.php:36
 * @route '/editor/folders/update/{folder_id}'
 */
export const update = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(args, options),
    method: 'get',
})

update.definition = {
    methods: ["get","head"],
    url: '/editor/folders/update/{folder_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\FolderController::update
 * @see app/Http/Controllers/Admin/FolderController.php:36
 * @route '/editor/folders/update/{folder_id}'
 */
update.url = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{folder_id}', parsedArgs.folder_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\FolderController::update
 * @see app/Http/Controllers/Admin/FolderController.php:36
 * @route '/editor/folders/update/{folder_id}'
 */
update.get = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\FolderController::update
 * @see app/Http/Controllers/Admin/FolderController.php:36
 * @route '/editor/folders/update/{folder_id}'
 */
update.head = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: update.url(args, options),
    method: 'head',
})
const folder = {
    create: Object.assign(create, create),
update: Object.assign(update, update),
post: Object.assign(post, post),
}

export default folder