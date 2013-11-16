TVS Web Control System
================================

Functionality
================================
*	The controller classes `UniFI` and `TVS-WebSys` are in it's initial state
*	The initial idea for the whole thing are complete
*	The DB Model is in it's initial state as well


UniFI Class
================================
*	File: **inc/unifi.class.php**	-	`Controller` class of **Ubiquiti UniFI**

####	Controller bootstrap (initialization)

*	Function: Start the `Control`


		$unifiman		=	new UNIFI_CONTROL(UNIFI_URL, UNIFI_USER, UNIFI_PASS, UNIFI_MAX_TIME);

Args:

*	`UNIFI_URL` -> **UniFI** connection URL (e.g: `http://localhost:8443`)
*	`UNIFI_USER` -> **UniFI** connection username
*	`UNIFI_PASS` -> **UniFI** connection password
*	`UNIFI_MAX_TIME` Connection TTL in minutes (default: 30 minutes)

####	Login

*	Function: Login into **UniFI**
*	`Heads up!` -> *The data will be inserted on the controller initialization.*


		$unifiman->Login();


####	Logout

*	Function: Logout of **UniFI**
*	`Heads up!` -> *You must `ALWAYS` logout whenever you're done. e.g: Page processing has ended.*


		$unifiman->Logout();


####	Authorize client

*	Function: Authorize a new client that has connected to the `Guest Portal`
*	`Heads up!` -> *When the time expire, a new session will be requested.*


		$unifiman->AuthorizeClient(MAC_ADDRESS, TIME)

Args:

*	`MAC_ADDRESS` -> Client's MAC Address
*	`TIME` -> *The time in minutes, to stay connected (A.K.A, TTL xD).*


####	Block's the client access

*	Function: Block's the access for specified client based on his MAC Address
*	`Heads up!` -> *The block can only be undone, with the `UnBlockClient(Args)` function, or through the `UniFI` control-panel.*


		$unifiman->BlockClient(MAC_ADDRESS)

Args:

*	`MAC_ADDRESS` -> Client's MAC Address

####	Unblock's the client access

*	Function: Unblock's the access for specified client based on his MAC Address


		$unifiman->UnBlockClient(MAC_ADDRESS)

Args:

*	`MAC_ADDRESS` -> Client's MAC Address

####	Disconnect the client

*	Function:	Esta função desconecta o cliente do AP. Uso:
*	`Heads up!` -> *When you disconnect a client, he will be able to connect again.*


		$unifiman->DisconnectClient(MAC_ADDRESS)

Args:

*	`MAC_ADDRESS` -> Client's MAC Address

####	Restart Access Point
	
*	Function: Restart an Access Point based on it's MAC Address


		$unifiman->RestartAP(MAC_ADDRESS)

Args:

*	`MAC_ADDRESS` -> AP's MAC Address

####	Retrieve the `Access Points` list
	
*	Function: Returns an `AP` list that is configured in your **UniFI**.


		$aps = $unifiman->GetAccessPoints()		

####	Retrieve the client's list
	
*	Function: Returns a list of all configured clients in your **UniFI**.


		$clients = $unifiman->GetClients()	


TVSWEB_Control Class
================================
*	File: **inc/tvswebsys.class.php**
This is the `Portal` **Controller** class.
Implemented functions:

####	Controller bootstrap (initialization)
	
*	Function: Initialize the controller

		$tvswebsys	=	new TVSWEB_CONTROL(HOST, USER, PASS, DB);

Args:

*	`HOST`	MySQL Host (IP)
*	`USER`	MySQL Username
*	`PASS`	MySQL Password
*	`DB` 	MySQL Database

**TODO**


Dependencies
================================
*	Finish the documentation of the **TVSWEB_Control** class
*	Creates an admin system for this
*	Create some beautiful templates haha

Project
================================
*   Made by `Lucas Teske` to `Teske Virtual System Ltda.`
*   Now released under the `GPLv3` license

Referred projects
================================
*	**Mobile Detect**: [https://github.com/serbanghita/Mobile-Detect][1] (Used to detect the client)
*	**UniFI API**:	[https://github.com/calmh/unifi-api][2]	(Re-written to PHP)

[1]:	https://github.com/serbanghita/Mobile-Detect
[2]:	https://github.com/calmh/unifi-api
