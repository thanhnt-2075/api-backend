<?php

declare(strict_types=1);

namespace Reconmap;

use Laminas\Diactoros\ResponseFactory;
use League\Container\Container;
use League\Route\RouteGroup;
use League\Route\Router;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\AuditLog\GetAuditLogStatsController;
use Reconmap\Controllers\AuditLog\ExportAuditLogController;
use Reconmap\Controllers\AuditLog\GetAuditLogController;
use Reconmap\Controllers\Projects\DeleteProjectController;
use Reconmap\Controllers\Projects\GetProjectController;
use Reconmap\Controllers\Projects\GetProjectsController;
use Reconmap\Controllers\Projects\GetProjectTargetsController;
use Reconmap\Controllers\Projects\GetProjectTasksController;
use Reconmap\Controllers\Vulnerabilities\GetVulnerabilitiesController;
use Reconmap\Controllers\Vulnerabilities\GetVulnerabilityController;
use Reconmap\Controllers\Vulnerabilities\DeleteVulnerabilityController;
use Reconmap\Controllers\IndexController;
use Reconmap\Controllers\Projects\CloneProjectController;
use Reconmap\Controllers\Projects\GetProjectVulnerabilitiesController;
use Reconmap\Controllers\Reports\GenerateReportController;
use Reconmap\Controllers\Reports\GetReportsController;
use Reconmap\Controllers\Users\UsersLoginController;

class ApiRouter extends Router
{

    public function mapRoutes(Container $container, Logger $logger): void
    {
        $authMiddleware = new AuthMiddleware($logger);

        $responseFactory = new ResponseFactory;
        $strategy = new \League\Route\Strategy\JsonStrategy($responseFactory);
        $strategy->setContainer($container);

        $this->setStrategy($strategy);

        // OPTIONS to support CORS
        $this->map('OPTIONS', '/{any:.*}', function (ServerRequestInterface $request): ResponseInterface {
            $response = (new \GuzzleHttp\Psr7\Response)->withStatus(200);
            return $response
                ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE')
                ->withHeader('Access-Control-Allow-Headers', 'Authorization')
                ->withHeader('Access-Control-Allow-Origin', '*');
        });

        $this->map('GET', '/', IndexController::class);
        $this->map('POST', '/users/login', UsersLoginController::class);
        $this->group('', function (RouteGroup $router): void {
            (new TasksRouter)->mapRoutes($router);
            (new UsersRouter)->mapRoutes($router);

            $router->map('GET', '/vulnerabilities', GetVulnerabilitiesController::class);
            $router->map('GET', '/vulnerabilities/{id:number}', GetVulnerabilityController::class);
            $router->map('DELETE', '/vulnerabilities/{id:number}', DeleteVulnerabilityController::class);
            $router->map('GET', '/auditlog', GetAuditLogController::class);
            $router->map('GET', '/auditlog/export', ExportAuditLogController::class);
            $router->map('GET', '/auditlog/stats', GetAuditLogStatsController::class);
            $router->map('GET', '/reports', GetReportsController::class);
            $router->map('GET', '/projects', GetProjectsController::class);
            $router->map('GET', '/projects/{id:number}', GetProjectController::class);
            $router->map('GET', '/projects/{id:number}/report', GenerateReportController::class);
            $router->map('POST', '/projects/{id:number}/clone', CloneProjectController::class);
            $router->map('GET', '/projects/{id:number}/tasks', GetProjectTasksController::class);
            $router->map('GET', '/projects/{id:number}/targets', GetProjectTargetsController::class);
            $router->map('GET', '/projects/{id:number}/vulnerabilities', GetProjectVulnerabilitiesController::class);
            $router->map('DELETE', '/projects/{id:number}', DeleteProjectController::class);
        })->middleware($authMiddleware);
    }
}
