import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\DocumentController::create
 * @see app/Http/Controllers/Admin/DocumentController.php:29
 * @route '/editor/documents/store'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

create.definition = {
    methods: ["post"],
    url: '/editor/documents/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DocumentController::create
 * @see app/Http/Controllers/Admin/DocumentController.php:29
 * @route '/editor/documents/store'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DocumentController::create
 * @see app/Http/Controllers/Admin/DocumentController.php:29
 * @route '/editor/documents/store'
 */
create.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\DocumentController::update
 * @see app/Http/Controllers/Admin/DocumentController.php:29
 * @route '/editor/documents/store/{document_id}'
 */
export const update = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/editor/documents/store/{document_id}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DocumentController::update
 * @see app/Http/Controllers/Admin/DocumentController.php:29
 * @route '/editor/documents/store/{document_id}'
 */
update.url = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{document_id}', parsedArgs.document_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DocumentController::update
 * @see app/Http/Controllers/Admin/DocumentController.php:29
 * @route '/editor/documents/store/{document_id}'
 */
update.post = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})
const post = {
    create: Object.assign(create, create),
update: Object.assign(update, update),
}

export default post