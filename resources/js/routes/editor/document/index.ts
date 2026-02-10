import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import post from './post'
/**
* @see \App\Http\Controllers\Admin\DocumentController::create
 * @see app/Http/Controllers/Admin/DocumentController.php:112
 * @route '/editor/documents/create/p/{parent_id}'
 */
export const create = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(args, options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/editor/documents/create/p/{parent_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DocumentController::create
 * @see app/Http/Controllers/Admin/DocumentController.php:112
 * @route '/editor/documents/create/p/{parent_id}'
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
* @see \App\Http\Controllers\Admin\DocumentController::create
 * @see app/Http/Controllers/Admin/DocumentController.php:112
 * @route '/editor/documents/create/p/{parent_id}'
 */
create.get = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DocumentController::create
 * @see app/Http/Controllers/Admin/DocumentController.php:112
 * @route '/editor/documents/create/p/{parent_id}'
 */
create.head = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\DocumentController::create
 * @see app/Http/Controllers/Admin/DocumentController.php:112
 * @route '/editor/documents/create/p/{parent_id}'
 */
    const createForm = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\DocumentController::create
 * @see app/Http/Controllers/Admin/DocumentController.php:112
 * @route '/editor/documents/create/p/{parent_id}'
 */
        createForm.get = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\DocumentController::create
 * @see app/Http/Controllers/Admin/DocumentController.php:112
 * @route '/editor/documents/create/p/{parent_id}'
 */
        createForm.head = (args: { parent_id: string | number } | [parent_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\Admin\DocumentController::update
 * @see app/Http/Controllers/Admin/DocumentController.php:128
 * @route '/editor/documents/update/{document_id}'
 */
export const update = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(args, options),
    method: 'get',
})

update.definition = {
    methods: ["get","head"],
    url: '/editor/documents/update/{document_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DocumentController::update
 * @see app/Http/Controllers/Admin/DocumentController.php:128
 * @route '/editor/documents/update/{document_id}'
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
 * @see app/Http/Controllers/Admin/DocumentController.php:128
 * @route '/editor/documents/update/{document_id}'
 */
update.get = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DocumentController::update
 * @see app/Http/Controllers/Admin/DocumentController.php:128
 * @route '/editor/documents/update/{document_id}'
 */
update.head = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: update.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\DocumentController::update
 * @see app/Http/Controllers/Admin/DocumentController.php:128
 * @route '/editor/documents/update/{document_id}'
 */
    const updateForm = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: update.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\DocumentController::update
 * @see app/Http/Controllers/Admin/DocumentController.php:128
 * @route '/editor/documents/update/{document_id}'
 */
        updateForm.get = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: update.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\DocumentController::update
 * @see app/Http/Controllers/Admin/DocumentController.php:128
 * @route '/editor/documents/update/{document_id}'
 */
        updateForm.head = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Admin\DocumentController::deleteMethod
 * @see app/Http/Controllers/Admin/DocumentController.php:149
 * @route '/editor/documents/delete/{document_id}'
 */
export const deleteMethod = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/editor/documents/delete/{document_id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\DocumentController::deleteMethod
 * @see app/Http/Controllers/Admin/DocumentController.php:149
 * @route '/editor/documents/delete/{document_id}'
 */
deleteMethod.url = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return deleteMethod.definition.url
            .replace('{document_id}', parsedArgs.document_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DocumentController::deleteMethod
 * @see app/Http/Controllers/Admin/DocumentController.php:149
 * @route '/editor/documents/delete/{document_id}'
 */
deleteMethod.delete = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\DocumentController::deleteMethod
 * @see app/Http/Controllers/Admin/DocumentController.php:149
 * @route '/editor/documents/delete/{document_id}'
 */
    const deleteMethodForm = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteMethod.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DocumentController::deleteMethod
 * @see app/Http/Controllers/Admin/DocumentController.php:149
 * @route '/editor/documents/delete/{document_id}'
 */
        deleteMethodForm.delete = (args: { document_id: string | number } | [document_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteMethod.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    deleteMethod.form = deleteMethodForm
const document = {
    create: Object.assign(create, create),
update: Object.assign(update, update),
post: Object.assign(post, post),
delete: Object.assign(deleteMethod, deleteMethod),
}

export default document