TVS Web Control System
================================
	 _______     ______
	|_   _\ \   / / ___| 
	  | |  \ \ / /\___ \ 
	  | |   \ V /  ___) |
	  |_|    \_/  |____/ 
                     

TODO: Port this to english.

Funcionalidades
================================
*	As classes de controle do **UniFI** e **TVS-WebSys** estão em seu estado inicial e `completos` em relação a ideia inicial.
*	O modelo de Banco de Dados está no seu estado inicial


Classe UniFI
================================
*	Arquivo: **inc/unifi.class.php**	-	Esta é a classe de **controle** do **Ubiquiti UniFI**

####	Inicialização do Controle

*	Função: Inicializar o Control


		$unifiman		=	new UNIFI_CONTROL(UNIFI_URL,UNIFI_USER, UNIFI_PASS, UNIFI_MAX_TIME);

Sendo:

*	`UNIFI_URL` a URL de conexão ao **UniFI** ( Ex: **http://IP:8443** )
*	`UNIFI_USER` o usuário para se conectar ao **UniFI**
*	`UNIFI_PASS` a senha usada para se conectar **UniFI**
*	`UNIFI_MAX_TIME` o tempo máximo que o usuário ficará conectado em minutos. (Padrão: 30 minutos)

####	Login

*	Função:	Efetua o Login no **UniFI**.
*	Obs:	`Será usado os dados inseridos na inicialização do controle.`


		$unifiman->Login();


####	Logout

*	Função:	Efetua o Logout no **UniFI**
*	Obs:	`Deve-se efetuar o logout **SEMPRE** que se encerrar todas as operações. Ex: Final do processo da página.`


		$unifiman->Logout();


####	Autorizar Cliente

*	Função:	Autoriza um cliente que se conecta ao Guest Portal.
*	Obs:	`Quando o tempo expirar, uma nova sessão será requisitada.`


		$unifiman->AuthorizeClient(MAC_ADDRESS, TEMPO)

Onde:

*	`MAC_ADDRESS` é o Mac Address do Cliente
*	`TEMPO` é o tempo em minutos que ele poderá ficar conectado.


####	Bloquear Acesso do Cliente

*	Função:	Bloqueia o acesso de um cliente baseado no Mac Address dele.
*	Obs:	`O bloqueio só poderá ser desfeito com a função de desbloqueio, ou pelo painel de controle do **UniFI**`


		$unifiman->BlockClient(MAC_ADDRESS)

Onde:

*	`MAC_ADDRESS` é o Mac Address do Cliente

####	Desbloquear Acesso do Cliente

*	Função:	Desbloqueia o acesso de um cliente baseado no Mac Address dele.


		$unifiman->UnBlockClient(MAC_ADDRESS)

Onde:

*	`MAC_ADDRESS` é o Mac Address do Cliente

####	Desconectar Cliente

*	Função:	Esta função desconecta o cliente do AP. Uso:
*	Obs: 	`Ao desconectar o cliente, ele poderá voltar a se conectar denovo.`


		$unifiman->DisconnectClient(MAC_ADDRESS)

Onde:

*	`MAC_ADDRESS` é o Mac Address do Cliente

####	Reiniciar Access Point
	
*	Função:	Reinicia um AP pelo seu Mac Address.


		$unifiman->RestartAP(MAC_ADDRESS)

Onde:

*	`MAC_ADDRESS` é o Mac Address do AP

####	Obter Lista de Access Points
	
*	Função:	Retorna uma lista de APs que estão configurados no seu **UniFI**.


		$aps = $unifiman->GetAccessPoints()		

####	Obter Lista de Clientes
	
*	Função:	Retorna uma lista de Cliente que estão configurados no seu **UniFI**.


		$clients = $unifiman->GetClients()	


Classe TVSWEB_Control
================================
*	Arquivo: **inc/tvswebsys.class.php**
Esta é a classe de **controle** do portal.
Funções implementadas:

####	Inicialização do controle
	
*	Função:	Inicializa o controle.

		$tvswebsys	=	new TVSWEB_CONTROL(HOST,USER,PASS,DB);

Sendo:

*	`HOST`	o IP do servidor MySQL
*	`USER`	o usuário para conexão no MySQL
*	`PASS`	a senha para conexão no MySQL
*	`DB` 	o banco de dados a se conectar

**TODO**


Pendências
================================
*	Terminar documentação da Classe **TVSWEB_Control**
*	Criar sistema de administração 
*	Criar templates bonitos

Projeto
================================
*   Feito por Lucas Teske para Teske Virtual System Ltda.
*   Projeto anteriormente proprietário agora liberado sob a licença GPLv3

Projetos Referidos
================================
*	**Mobile Detect**: [https://github.com/serbanghita/Mobile-Detect][1]	(Usado para deteção de cliente)
*	**UniFI API**:	[https://github.com/calmh/unifi-api][2]	(Reescrito para PHP)

[1]:	https://github.com/serbanghita/Mobile-Detect
[2]:	https://github.com/calmh/unifi-api
