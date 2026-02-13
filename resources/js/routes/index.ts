import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../wayfinder'
/**
* @see \App\Http\Controllers\MainController::__invoke
 * @see app/Http/Controllers/MainController.php:12
 * @route '/'
 */
export const home = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})

home.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MainController::__invoke
 * @see app/Http/Controllers/MainController.php:12
 * @route '/'
 */
home.url = (options?: RouteQueryOptions) => {
    return home.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\MainController::__invoke
 * @see app/Http/Controllers/MainController.php:12
 * @route '/'
 */
home.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\MainController::__invoke
 * @see app/Http/Controllers/MainController.php:12
 * @route '/'
 */
home.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: home.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:8
 * @route '/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logout.url(options),
    method: 'get',
})

logout.definition = {
    methods: ["get","head"],
    url: '/logout',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:8
 * @route '/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:8
 * @route '/logout'
 */
logout.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logout.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:8
 * @route '/logout'
 */
logout.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: logout.url(options),
    method: 'head',
})