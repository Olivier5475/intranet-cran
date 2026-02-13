import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
import user3b1c2b from './user'
import departementsAe8273 from './departements'
/**
* @see \App\Http\Controllers\Admin\UsersController::user
 * @see app/Http/Controllers/Admin/UsersController.php:19
 * @route '/admin/users'
 */
export const user = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: user.url(options),
    method: 'get',
})

user.definition = {
    methods: ["get","head"],
    url: '/admin/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\UsersController::user
 * @see app/Http/Controllers/Admin/UsersController.php:19
 * @route '/admin/users'
 */
user.url = (options?: RouteQueryOptions) => {
    return user.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UsersController::user
 * @see app/Http/Controllers/Admin/UsersController.php:19
 * @route '/admin/users'
 */
user.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: user.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\UsersController::user
 * @see app/Http/Controllers/Admin/UsersController.php:19
 * @route '/admin/users'
 */
user.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: user.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\DepartementController::departements
 * @see app/Http/Controllers/Admin/DepartementController.php:20
 * @route '/admin/departements'
 */
export const departements = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: departements.url(options),
    method: 'get',
})

departements.definition = {
    methods: ["get","head"],
    url: '/admin/departements',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DepartementController::departements
 * @see app/Http/Controllers/Admin/DepartementController.php:20
 * @route '/admin/departements'
 */
departements.url = (options?: RouteQueryOptions) => {
    return departements.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DepartementController::departements
 * @see app/Http/Controllers/Admin/DepartementController.php:20
 * @route '/admin/departements'
 */
departements.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: departements.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DepartementController::departements
 * @see app/Http/Controllers/Admin/DepartementController.php:20
 * @route '/admin/departements'
 */
departements.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: departements.url(options),
    method: 'head',
})
const admin = {
    user: Object.assign(user, user3b1c2b),
departements: Object.assign(departements, departementsAe8273),
}

export default admin