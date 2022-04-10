<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory {

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter(Nette\DI\Container $container) {

		$router = new RouteList;
        $router[] = $actionsRouter = new RouteList("Actions");
        $actionsRouter[] = new Route("api/actions/", "ActionsApi:plannedAction");
        $actionsRouter[] = new Route("login/", "Sign:login");
        $actionsRouter[] = new Route("actions/", "Dashboard:");
        $actionsRouter[] = new Route("actions/dashboard/", "Dashboard:");
        $actionsRouter[] = new Route("actions/actions/", "Actions:");
        $actionsRouter[] = new Route("actions/actions/free/", "Actions:free");
        $actionsRouter[] = new Route("actions/actions/detail/<action_id>/", "Actions:detail");
        $actionsRouter[] = new Route("actions/children/", "Children:list");
        $actionsRouter[] = new Route("actions/children/addChild/", "Children:addChild");
        $actionsRouter[] = new Route("actions/stuff/list/", "Stuff:stuff");

        $router[] = $journalRouter = new RouteList("Journal");
        $journalRouter[] = new Route("journal/actions", "Journal:actions");

        $router[] = $dashboardRouter = new RouteList("Dashboard");

        $router[] = $appRouter = new RouteList("");
        $appRouter[] = new Route("profile/user/<userid>/", "User:userProfile");
        $appRouter[] = new Route("profile/certificate/<userid>/<certid>/", "User:certificate");
        $appRouter[] = new Route("stuff/", "Stuff:stuff");
        $appRouter[] = new Route("users/", "User:users");
        $appRouter[] = new Route("users/add/", "User:addUser");
        $appRouter[] = new Route("certificates/list/", "CertificatesTypes:list");
        $appRouter[] = new Route("calendar/", "Calendar:show");
        $appRouter[] = new Route("types/list/", "Types:list");

        $appRouter[] = new Route("settings/", "Settings:default");
        $appRouter[] = new Route("help/", "Help:default");
        $appRouter[] = new Route("", "Help:default");

		return $router;
	}

}
