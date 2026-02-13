import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
import post from './post'
/**
* @see \App\Http\Controllers\Admin\FileController::create
 * @see app/Http/Controllers/Admin/FileController.php:86
 * @route '/editor/files/create/p/{parent_id}'
 */
export const create = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(args, options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/editor/files/create/p/{parent_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\FileController::create
 * @see app/Http/Controllers/Admin/FileController.php:86
 * @route '/editor/files/create/p/{parent_id}'
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
* @see \App\Http\Controllers\Admin\FileController::create
 * @see app/Http/Controllers/Admin/FileController.php:86
 * @route '/editor/files/create/p/{parent_id}'
 */
create.get = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\FileController::create
 * @see app/Http/Controllers/Admin/FileController.php:86
 * @route '/editor/files/create/p/{parent_id}'
 */
create.head = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\FileController::update
 * @see app/Http/Controllers/Admin/FileController.php:94
 * @route '/editor/files/update/{file_id}'
 */
export const update = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(args, options),
    method: 'get',
})

update.definition = {
    methods: ["get","head"],
    url: '/editor/files/update/{file_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\FileController::update
 * @see app/Http/Controllers/Admin/FileController.php:94
 * @route '/editor/files/update/{file_id}'
 */
update.url = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { file_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    file_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        file_id: args.file_id,
                }

    return update.definition.url
            .replace('{file_id}', parsedArgs.file_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\FileController::update
 * @see app/Http/Controllers/Admin/FileController.php:94
 * @route '/editor/files/update/{file_id}'
 */
update.get = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\FileController::update
 * @see app/Http/Controllers/Admin/FileController.php:94
 * @route '/editor/files/update/{file_id}'
 */
update.head = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: update.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\VersionController::history
 * @see app/Http/Controllers/Admin/VersionController.php:26
 * @route '/editor/files/history/{file_id}'
 */
export const history = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(args, options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/editor/files/history/{file_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\VersionController::history
 * @see app/Http/Controllers/Admin/VersionController.php:26
 * @route '/editor/files/history/{file_id}'
 */
history.url = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { file_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    file_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        file_id: args.file_id,
                }

    return history.definition.url
            .replace('{file_id}', parsedArgs.file_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\VersionController::history
 * @see app/Http/Controllers/Admin/VersionController.php:26
 * @route '/editor/files/history/{file_id}'
 */
history.get = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\VersionController::history
 * @see app/Http/Controllers/Admin/VersionController.php:26
 * @route '/editor/files/history/{file_id}'
 */
history.head = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\FileController::deleteMethod
 * @see app/Http/Controllers/Admin/FileController.php:111
 * @route '/editor/files/delete/{file_id}'
 */
export const deleteMethod = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/editor/files/delete/{file_id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\FileController::deleteMethod
 * @see app/Http/Controllers/Admin/FileController.php:111
 * @route '/editor/files/delete/{file_id}'
 */
deleteMethod.url = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { file_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    file_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        file_id: args.file_id,
                }

    return deleteMethod.definition.url
            .replace('{file_id}', parsedArgs.file_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\FileController::deleteMethod
 * @see app/Http/Controllers/Admin/FileController.php:111
 * @route '/editor/files/delete/{file_id}'
 */
deleteMethod.delete = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})
const file = {
    create: Object.assign(create, create),
update: Object.assign(update, update),
history: Object.assign(history, history),
post: Object.assign(post, post),
delete: Object.assign(deleteMethod, deleteMethod),
}

export default file