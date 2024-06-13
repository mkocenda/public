<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory
{

	/**
	 * @param Nette\DI\Container $container
	 * @return RouteList
	 */
	public static function createRouter(Nette\DI\Container $container)
	{
		$router = new RouteList;

		$router[] = $appRouter = new RouteList("");
		$appRouter[] = new Route("", "App:Dashboard:default");
		$appRouter[] = new Route("/settings/", "App:Dashboard:settings");

        /** Administration */
		$appRouter[] = new Route("/admin/", "Admin:Dashboard:");
		$appRouter[] = new Route("/admin/users/list/", "Admin:Users:usersList");
		$appRouter[] = new Route("/admin/roles/list/", "Admin:Users:rolesList");
		$appRouter[] = new Route("/admin/roles/addright/", "Admin:Users:addRight");
		$appRouter[] = new Route("/admin/organisations/list/", "Admin:AdmOrganisations:list");
		$appRouter[] = new Route("/admin/stuffs/list/", "Admin:AdmStuffs:list");
		$appRouter[] = new Route("/admin/actions/list/", "Admin:AdmActions:list");

        /** Application */
		$appRouter[] = new Route("/actions/planned/", "App:AppActions:plannedList");
		$appRouter[] = new Route("/actions/running/", "App:AppActions:runningList");
		$appRouter[] = new Route("/actions/done/", "App:AppActions:doneList");

        $appRouter[] = new Route("/participants/list/", "App:Participants:list");
        $appRouter[] = new Route("/participants/parents/", "App:Participants:parentsList");

        $appRouter[] = new Route("/logs/running/list/", "App:LogActions:runningList");
        $appRouter[] = new Route("/logs/done/list/", "App:LogActions:doneList");
        $appRouter[] = new Route("/logs/planned/list/", "App:LogActions:plannedList");

        $appRouter[] = new Route("/warehouses/list/", "App:Warehouse:list");
        $appRouter[] = new Route("/parts/list/", "App:Parts:list");
		
		$appRouter[] = new Route("/certificates/list/", "App:Certificates:list");
		$appRouter[] = new Route("/stuffs/list/", "App:Stuff:list");
		$appRouter[] = new Route("/stuff/edit/<stuff_id>/", "App:Stuff:edit");
		$appRouter[] = new Route("/stuff/roles/", "App:Stuff:roles");
		
		
    	$appRouter[] = new Route("/login/", "User:login");
		$appRouter[] = new Route("/logout/", "User:logout");
		$appRouter[] = new Route("/forget/", "User:forget");
		$appRouter[] = new Route("/forget/info/", "User:forgetinfo");
		$appRouter[] = new Route("/reset/<confirm_hash>/", "User:reset");
		$appRouter[] = new Route("/success/", "User:resetsuccess");
		$appRouter[] = new Route("/about/", "App:Apps:about");
		$appRouter[] = new Route("/adam/", "App:Apps:adam");
		$appRouter[] = new Route("/user/getUserMessages/", "App:Base:GetUserMessages");
		$appRouter[] = new Route("/user/messages/", "App:UsersMessages:userMessages");
		$appRouter[] = new Route("/user/messages/message/<id>/", "App:UsersMessages:userMessages");

        /** API/ */
		$appRouter[] = new Route("/api/v1/actions/list/", "Api:Actions:actionsList");
		$appRouter[] = new Route("/api/v1/stuffs/list/", "Api:Stuffs:stuffsList");
		$appRouter[] = new Route("/api/v1/stuff/detail/<stuff_id>/", "Api:Stuffs:stuffDetail");
		$appRouter[] = new Route("/api/v1/participants/list/", "Api:Participants:participantsList");
		$appRouter[] = new Route("/api/v1/pills/list/", "Api:Pills:getPillsList");
		$appRouter[] = new Route("/api/v1/login/", "Api:Login:login");

		/** CRON */
		$appRouter[] = new Route("/cron/send/", "Cron:send");
		$appRouter[] = new Route("/cron/run/", "Cron:run");
		$appRouter[] = new Route("/cron/alert/", "Cron:alert");
		$appRouter[] = new Route("/cron/medicaments/", "Cron:medicaments");
		
		return $router;
	}

}


