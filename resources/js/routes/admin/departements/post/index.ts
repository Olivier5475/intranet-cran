import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\DepartementController::create
 * @see app/Http/Controllers/Admin/DepartementController.php:27
 * @route '/admin/departements'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

create.definition = {
    methods: ["post"],
    url: '/admin/departements',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DepartementController::create
 * @see app/Http/Controllers/Admin/DepartementController.php:27
 * @route '/admin/departements'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DepartementController::create
 * @see app/Http/Controllers/Admin/DepartementController.php:27
 * @route '/admin/departements'
 */
create.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\DepartementController::create
 * @see app/Http/Controllers/Admin/DepartementController.php:27
 * @route '/admin/departements'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: create.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DepartementController::create
 * @see app/Http/Controllers/Admin/DepartementController.php:27
 * @route '/admin/departements'
 */
        createForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: create.url(options),
            method: 'post',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\Admin\DepartementController::update
 * @see app/Http/Controllers/Admin/DepartementController.php:27
 * @route '/admin/departements/{id}'
 */
export const update = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/admin/departements/{id}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\DepartementController::update
 * @see app/Http/Controllers/Admin/DepartementController.php:27
 * @route '/admin/departements/{id}'
 */
update.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DepartementController::update
 * @see app/Http/Controllers/Admin/DepartementController.php:27
 * @route '/admin/departements/{id}'
 */
update.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\DepartementController::update
 * @see app/Http/Controllers/Admin/DepartementController.php:27
 * @route '/admin/departements/{id}'
 */
    const updateForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DepartementController::update
 * @see app/Http/Controllers/Admin/DepartementController.php:27
 * @route '/admin/departements/{id}'
 */
        updateForm.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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