<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoutePathsTest extends TestCase
{
    /**
     * The production host serves these extensions as static files, so a route
     * ending in one never reaches PHP and returns the web server's own 404. It
     * cost the poll link preview and the article sharing images before anyone
     * noticed, because both worked locally.
     *
     * robots.txt and the build output are real files in public/ and are fine.
     * sitemap.xml is a route and does reach PHP, so .xml is not in the list.
     */
    public function test_no_route_path_ends_in_an_extension_the_web_server_serves_itself(): void
    {
        foreach (Route::getRoutes() as $route) {
            // Livewire's dynamic JavaScript route hits the same wall. It is already
            // handled by publishing Livewire's static assets on every release, and
            // the deploy script verifies the served file against the deployed one.
            if (str_starts_with($route->uri(), 'livewire')) {
                continue;
            }
            $this->assertDoesNotMatchRegularExpression(
                '~\.(png|jpe?g|webp|gif|svg|ico|js|mjs|css|map|woff2?)$~i',
                $route->uri(),
                "Route {$route->uri()} ends in an extension the web server serves as a static file."
            );
        }
    }
}
