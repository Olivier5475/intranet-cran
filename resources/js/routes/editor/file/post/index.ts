import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\FileController::create
 * @see app/Http/Controllers/Admin/FileController.php:25
 * @route '/editor/files/store'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

create.definition = {
    methods: ["post"],
    url: '/editor/files/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\FileController::create
 * @see app/Http/Controllers/Admin/FileController.php:25
 * @route '/editor/files/store'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\FileController::create
 * @see app/Http/Controllers/Admin/FileController.php:25
 * @route '/editor/files/store'
 */
create.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\FileController::update
 * @see app/Http/Controllers/Admin/FileController.php:25
 * @route '/editor/files/store/{file_id}'
 */
export const update = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/editor/files/store/{file_id}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\FileController::update
 * @see app/Http/Controllers/Admin/FileController.php:25
 * @route '/editor/files/store/{file_id}'
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
 * @see app/Http/Controllers/Admin/FileController.php:25
 * @route '/editor/files/store/{file_id}'
 */
update.post = (args: { file_id: string | number } | [file_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})
const post = {
    create: Object.assign(create, create),
update: Object.assign(update, update),
}

export default post