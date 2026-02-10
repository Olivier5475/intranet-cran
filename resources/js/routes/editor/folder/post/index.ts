import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\FolderController::create
 * @see app/Http/Controllers/Admin/FolderController.php:59
 * @route '/editor/folders/store'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

create.definition = {
    methods: ["post"],
    url: '/editor/folders/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\FolderController::create
 * @see app/Http/Controllers/Admin/FolderController.php:59
 * @route '/editor/folders/store'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\FolderController::create
 * @see app/Http/Controllers/Admin/FolderController.php:59
 * @route '/editor/folders/store'
 */
create.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\FolderController::create
 * @see app/Http/Controllers/Admin/FolderController.php:59
 * @route '/editor/folders/store'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: create.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\FolderController::create
 * @see app/Http/Controllers/Admin/FolderController.php:59
 * @route '/editor/folders/store'
 */
        createForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: create.url(options),
            method: 'post',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\Admin\FolderController::update
 * @see app/Http/Controllers/Admin/FolderController.php:59
 * @route '/editor/folders/store/{folder_id}'
 */
export const update = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/editor/folders/store/{folder_id}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\FolderController::update
 * @see app/Http/Controllers/Admin/FolderController.php:59
 * @route '/editor/folders/store/{folder_id}'
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
 * @see app/Http/Controllers/Admin/FolderController.php:59
 * @route '/editor/folders/store/{folder_id}'
 */
update.patch = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\FolderController::update
 * @see app/Http/Controllers/Admin/FolderController.php:59
 * @route '/editor/folders/store/{folder_id}'
 */
    const updateForm = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\FolderController::update
 * @see app/Http/Controllers/Admin/FolderController.php:59
 * @route '/editor/folders/store/{folder_id}'
 */
        updateForm.patch = (args: { folder_id: string | number } | [folder_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const post = {
    create: Object.assign(create, create),
update: Object.assign(update, update),
}

export default post