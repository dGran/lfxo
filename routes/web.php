<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authenticate routes
Auth::routes();
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
Route::get('/user/verify/{token}', 'Auth\RegisterController@verifyUser')->name('verifyUser');
Route::get('/user/resend_verify', 'Auth\LoginController@resendActivation')->name('resendActivation');
Route::post('/user/resend_verify', 'Auth\LoginController@resendActivationMail')->name('resendActivation.mail');
// Route::get('/user/resend_verify/{token}', 'Auth\LoginController@resendActivationMail')->name('resendActivationMail');

// Home Routes
Route::get('/', 'HomeController@index')->name('home');
Route::get('/noticias/{type?}', 'HomeController@posts')->name('home.posts');
// Route::get('/{type?}', 'HomeController@index')->name('home');
Route::get('politica-privacidad', 'HomeController@privacity')->name('privacity');
Route::get('contacto', 'HomeController@contact')->name('contact');
Route::post('contacto/enviar', 'HomeController@contactSent')->name('contact.sent');

// Competitions routes
Route::middleware('check_active_season')->group(function () {

	Route::get('reglamento', 'HomeController@rules')->name('rules');

	Route::get('competiciones/{season_slug?}', 'CompetitionController@index')->name('competitions');
	Route::get('competiciones/clasificacion/{season_slug}/{competition_slug}/{phase_slug?}/{group_slug?}', 'CompetitionController@table')->name('competitions.table');
	Route::get('competiciones/partidos/{season_slug}/{competition_slug}/{phase_slug?}/{group_slug?}', 'CompetitionController@calendar')->name('competitions.calendar');
	Route::get('competiciones/partido/{match_id}', 'CompetitionController@match')->name('competitions.match');
	Route::get('competiciones/{season_slug}/{competition_slug}/partidos/editar/{match_id}', 'CompetitionController@editMatch')->name('competitions.calendar.match.edit');
	Route::put('competiciones/{season_slug}/{competition_slug}/partidos/editar/{match_id}', 'CompetitionController@updateMatch')->name('competitions.calendar.match.update');
	Route::get('competiciones/{season_slug}/{competition_slug}/partidos/detalle/{match_id}', 'CompetitionController@matchDetails')->name('competitions.calendar.match.details');
	Route::get('competiciones/estadisticas/{season_slug}/{competition_slug}/{participant_id?}', 'CompetitionController@stats')->name('competitions.stats');
});

// Clubs routes
Route::middleware('check_active_season')->group(function () {
	Route::get('clubs/{season_slug?}', 'ClubController@clubs')->name('clubs');
	Route::get('clubs/{season_slug}/{slug}', 'ClubController@club')->name('club');
	Route::get('clubs/{season_slug}/{slug}/plantilla', 'ClubController@clubRoster')->name('club.roster');
	Route::get('clubs/{season_slug}/{slug}/economia', 'ClubController@clubEconomy')->name('club.economy');
	Route::get('clubs/{season_slug}/{slug}/calendario', 'ClubController@clubCalendar')->name('club.calendar');
	Route::get('clubs/{season_slug}/{slug}/partidas-pendientes', 'ClubController@pendingMatches')->name('club.pending_matches');
	Route::get('clubs/{season_slug}/{slug}/sala-de-prensa', 'ClubController@clubPress')->name('club.press');
	Route::post('clubs/{season_slug}/{slug}/sala-de-prensa/nueva', 'ClubController@clubPressAdd')->name('club.press.add');
});

// Market Routes
Route::middleware('check_active_season')->group(function () {
	Route::get('mercado/resumen/{season_slug?}', 'MarketController@index')->name('market');
	Route::get('mercado/acuerdos/{season_slug?}', 'MarketController@agreements')->name('market.agreements');
	Route::get('mercado/buscador/{season_slug?}', 'MarketController@search')->name('market.search');
	Route::get('mercado/escaparate/{season_slug?}', 'MarketController@onSale')->name('market.sale');
	Route::get('mercado/equipos/{season_slug?}', 'MarketController@teams')->name('market.teams');
	Route::get('mercado/equipos/{season_slug}/{slug}', 'MarketController@team')->name('market.team');
	Route::get('mercado/mi-equipo', 'MarketController@myTeam')->name('market.my_team');
	Route::get('mercado/mi-equipo/jugador/{id}', 'MarketController@myTeamPlayer')->name('market.my_team.player');
	Route::get('mercado/mi-equipo/jugador/editar/{id}', 'MarketController@myTeamPlayerEdit')->name('market.my_team.player.edit');
	Route::put('mercado/mi-equipo/jugador/editar/{id}', 'MarketController@myTeamPlayerUpdate')->name('market.my_team.player.update');
	Route::get('mercado/mi-equipo/jugador/declarar-transferible/{id}', 'MarketController@tagsTransferable')->name('market.my_team.player.tags.transferable');
	Route::get('mercado/mi-equipo/jugador/declarar-intransferible/{id}', 'MarketController@tagsUntransferable')->name('market.my_team.player.tags.untransferable');
	Route::get('mercado/mi-equipo/jugador/declarar-cedible/{id}', 'MarketController@tagsOnLoan')->name('market.my_team.player.tags.on_loan');
	Route::get('mercado/mi-equipo/jugador/eliminar-etiquetas/{id}', 'MarketController@tagsDelete')->name('market.my_team.player.tags.delete');
	Route::get('mercado/mi-equipo/jugador/despedir/{id}', 'MarketController@dismiss')->name('market.my_team.player.dismiss');

	Route::get('mercado/negociaciones', 'MarketController@trades')->name('market.trades');
	Route::get('mercado/negociaciones/ofertas-recibidas', 'MarketController@tradesReceived')->name('market.trades.received');
	Route::get('mercado/negociaciones/ofertas-enviadas', 'MarketController@tradesSent')->name('market.trades.sent');

	Route::get('mercado/negociaciones/nueva/{participant_id}/{player_id?}', 'MarketController@tradesAdd')->name('market.trades.add');
	Route::post('mercado/negociaciones/nueva/{id}', 'MarketController@tradesSave')->name('market.trades.save');
	Route::get('mercado/negociaciones/{id}/aceptar', 'MarketController@tradesAccept')->name('market.trades.accept');
	Route::get('mercado/negociaciones/{id}/rechazar', 'MarketController@tradesDecline')->name('market.trades.decline');
	Route::get('mercado/negociaciones/{id}/retirar', 'MarketController@tradesRetire')->name('market.trades.retire');
	Route::get('mercado/negociaciones/{id}/eliminar', 'MarketController@tradesDelete')->name('market.trades.delete');

	Route::get('mercado/favoritos/{season_slug?}', 'MarketController@favorites')->name('market.favorites');
	Route::get('mercado/favoritos/eliminar/{id}', 'MarketController@favoritesDestroy')->name('market.favorites.destroy');
	// market utils routes
	Route::get('mercado/agregar-favorito/{player_id}/{participant_id}', 'MarketController@addFavoritePlayer')->name('market.favorite_player.add');
	Route::get('mercado/eliminar-favorito/{player_id}/{participant_id}', 'MarketController@removeFavoritePlayer')->name('market.favorite_player.remove');
	Route::get('mercado/fichar-jugador-libre/{id}', 'MarketController@signFreePlayer')->name('market.sign_free_player');
	Route::get('mercado/pagar-clausula-jugador/{id}', 'MarketController@payClausePlayer')->name('market.pay_clause_player');
	Route::get('mercado/fichar-ya-jugador/{id}', 'MarketController@signNowPlayer')->name('market.sign_now_player');
	Route::get('mercado/jugador/{id}', 'MarketController@playerView')->name('market.playerView');
});

// User routes
Route::get('perfil', 'ProfileController@edit')->name('profileEdit');
Route::put('perfil/{id}', 'ProfileController@update')->name('profileUpdate');
Route::get('notificaciones', 'MailboxController@index')->name('notifications');
Route::get('notificaciones/marcar-como-leida/{id}', 'MailboxController@read')->name('notifications.read');
Route::get('notificaciones/marcar-todas-como-leidas', 'MailboxController@readAll')->name('notifications.read_all');
Route::get('notificaciones/eliminar/{id}', 'MailboxController@destroy')->name('notifications.destroy');
Route::get('notificaciones/eliminar-todas', 'MailboxController@destroyAll')->name('notifications.destroy_all');


// Admin Routes
Route::middleware('auth', 'role:admin')->group(function () {

	Route::get('/admin', 'AdminController@dashboard')->name('admin');
	Route::get('/admin/logs/exportar/{filename}/{type}/{filterDescription}/{filterUser}/{filterTable}/{filterType}/{order}/{ids?}', 'AdminController@exportFile')->name('admin.export.file');
	Route::post('/admin/logs/importar', 'AdminController@importFile')->name('admin.import.file');

	Route::get('/admin/configuracion_general', 'AdminController@generalSettings')->name('admin.general_settings');

	//Games
	Route::get('/admin/juegos', 'GameController@index')->name('admin.games');
	Route::get('/admin/juegos/nuevo', 'GameController@add')->name('admin.games.add');
	Route::post('/admin/juegos/nuevo', 'GameController@save')->name('admin.games.save');
	Route::get('/admin/juegos/{id}', 'GameController@edit')->name('admin.games.edit');
	Route::put('/admin/juegos/{id}', 'GameController@update')->name('admin.games.update');
	Route::delete('/admin/juegos/eliminar/{id}', 'GameController@destroy')->name('admin.games.destroy');
	Route::get('/admin/juegos/eliminar-seleccionados/{ids}', 'GameController@destroyMany')->name('admin.games.destroy.many');
	Route::get('/admin/juegos/ver/{id}', 'GameController@view')->name('admin.games.view');
	Route::get('/admin/juegos/duplicar/{id}', 'GameController@duplicate')->name('admin.games.duplicate');
	Route::get('/admin/juegos/duplicar-seleccionados/{ids}', 'GameController@duplicateMany')->name('admin.games.duplicate.many');
	Route::get('/admin/juegos/exportar/{filename}/{type}/{filterName}/{filterCategory}/{order}/{ids?}', 'GameController@exportFile')->name('admin.games.export.file');
	Route::post('/admin/juegos/importar', 'GameController@importFile')->name('admin.games.import.file');

	//Teams Categories
	Route::get('/admin/categorias_equipos', 'TeamCategoryController@index')->name('admin.teams_categories');
	Route::get('/admin/categorias_equipos/nueva', 'TeamCategoryController@add')->name('admin.teams_categories.add');
	Route::post('/admin/categorias_equipos/nueva', 'TeamCategoryController@save')->name('admin.teams_categories.save');
	Route::get('/admin/categorias_equipos/{id}', 'TeamCategoryController@edit')->name('admin.teams_categories.edit');
	Route::put('/admin/categorias_equipos/{id}', 'TeamCategoryController@update')->name('admin.teams_categories.update');
	Route::delete('/admin/categorias_equipos/eliminar/{id}', 'TeamCategoryController@destroy')->name('admin.teams_categories.destroy');
	Route::get('/admin/categorias_equipos/eliminar-seleccionados/{ids}', 'TeamCategoryController@destroyMany')->name('admin.teams_categories.destroy.many');
	Route::get('/admin/categorias_equipos/duplicar/{id}', 'TeamCategoryController@duplicate')->name('admin.teams_categories.duplicate');
	Route::get('/admin/categorias_equipos/duplicar-seleccionados/{ids}', 'TeamCategoryController@duplicateMany')->name('admin.teams_categories.duplicate.many');
	Route::get('/admin/categorias_equipos/exportar/{filename}/{type}/{filterName}/{order}/{ids?}', 'TeamCategoryController@exportFile')->name('admin.teams_categories.export.file');
	Route::post('/admin/categorias_equipos/importar', 'TeamCategoryController@importFile')->name('admin.teams_categories.import.file');

	//Teams
	Route::get('/admin/equipos', 'TeamController@index')->name('admin.teams');
	Route::get('/admin/equipos/nuevo', 'TeamController@add')->name('admin.teams.add');
	Route::post('/admin/equipos/nuevo', 'TeamController@save')->name('admin.teams.save');
	Route::get('/admin/equipos/{id}', 'TeamController@edit')->name('admin.teams.edit');
	Route::put('/admin/equipos/{id}', 'TeamController@update')->name('admin.teams.update');
	Route::delete('/admin/equipos/eliminar/{id}', 'TeamController@destroy')->name('admin.teams.destroy');
	Route::get('/admin/equipos/eliminar-seleccionados/{ids}', 'TeamController@destroyMany')->name('admin.teams.destroy.many');
	Route::get('/admin/equipos/ver/{id}', 'TeamController@view')->name('admin.teams.view');
	Route::get('/admin/equipos/duplicar/{id}', 'TeamController@duplicate')->name('admin.teams.duplicate');
	Route::get('/admin/equipos/duplicar-seleccionados/{ids}', 'TeamController@duplicateMany')->name('admin.teams.duplicate.many');
	Route::get('/admin/equipos/exportar/{filename}/{type}/{filterName}/{filterCategory}/{order}/{ids?}', 'TeamController@exportFile')->name('admin.teams.export.file');
	Route::post('/admin/equipos/importar', 'TeamController@importFile')->name('admin.teams.import.file');

	//Players Databases
	Route::get('/admin/databases_jugadores', 'PlayerDBController@index')->name('admin.players_dbs');
	Route::get('/admin/databases_jugadores/nuevo', 'PlayerDBController@add')->name('admin.players_dbs.add');
	Route::post('/admin/databases_jugadores/nuevo', 'PlayerDBController@save')->name('admin.players_dbs.save');
	Route::get('/admin/databases_jugadores/{id}', 'PlayerDBController@edit')->name('admin.players_dbs.edit');
	Route::put('/admin/databases_jugadores/{id}', 'PlayerDBController@update')->name('admin.players_dbs.update');
	Route::delete('/admin/databases_jugadores/eliminar/{id}', 'PlayerDBController@destroy')->name('admin.players_dbs.destroy');
	Route::get('/admin/databases_jugadores/eliminar-seleccionados/{ids}', 'PlayerDBController@destroyMany')->name('admin.players_dbs.destroy.many');
	Route::get('/admin/databases_jugadores/duplicar/{id}', 'PlayerDBController@duplicate')->name('admin.players_dbs.duplicate');
	Route::get('/admin/databases_jugadores/duplicar-seleccionados/{ids}', 'PlayerDBController@duplicateMany')->name('admin.players_dbs.duplicate.many');
	Route::get('/admin/databases_jugadores/exportar/{filename}/{type}/{filterName}/{order}/{ids?}', 'PlayerDBController@exportFile')->name('admin.players_dbs.export.file');
	Route::post('/admin/databases_jugadores/importar', 'PlayerDBController@importFile')->name('admin.players_dbs.import.file');

	// Players
	Route::get('/admin/jugadores', 'PlayerController@index')->name('admin.players');
	Route::get('/admin/jugadores/nuevo', 'PlayerController@add')->name('admin.players.add');
	Route::post('/admin/jugadores/nuevo', 'PlayerController@save')->name('admin.players.save');
	Route::get('/admin/jugadores/{id}', 'PlayerController@edit')->name('admin.players.edit');
	Route::put('/admin/jugadores/{id}', 'PlayerController@update')->name('admin.players.update');
	Route::delete('/admin/jugadores/eliminar/{id}', 'PlayerController@destroy')->name('admin.players.destroy');
	Route::get('/admin/jugadores/eliminar-seleccionados/{ids}', 'PlayerController@destroyMany')->name('admin.players.destroy.many');
	Route::get('/admin/jugadores/ver/{id}', 'PlayerController@view')->name('admin.players.view');
	Route::get('/admin/jugadores/duplicar/{id}', 'PlayerController@duplicate')->name('admin.players.duplicate');
	Route::get('/admin/jugadores/duplicar-seleccionados/{ids}', 'PlayerController@duplicateMany')->name('admin.players.duplicate.many');
	Route::get('/admin/jugadores/exportar/{filename}/{type}/{filterName}/{filterCategory}/{filterTeam}/{filterNation}/{filterPosition}/{order}/{ids?}', 'PlayerController@exportFile')->name('admin.players.export.file');
	Route::get('/admin/jugadores/acciones/importar', 'PlayerController@importData')->name('admin.players.import.data');
	Route::post('/admin/jugadores/acciones/importar', 'PlayerController@importDataSave')->name('admin.players.import.data.save');
	Route::get('/admin/jugadores/acciones/enlazar-imagen/{id}/{www}', 'PlayerController@linkWebImage')->name('admin.players.link_web_image');
	Route::get('/admin/jugadores/acciones/enlazar-imagen-seleccionados/{ids}/{www}', 'PlayerController@linkWebImageMany')->name('admin.players.link_web_image.many');
	Route::get('/admin/jugadores/acciones/desenlazar_imagen/{id}', 'PlayerController@unlinkWebImage')->name('admin.players.unlink_web_image');
	Route::get('/admin/jugadores/acciones/desenlazar-imagen-seleccionados/{ids}', 'PlayerController@unlinkWebImageMany')->name('admin.players.unlink_web_image.many');
	Route::get('/admin/jugadores/acciones/enlazar_imagenes/{www}', 'PlayerController@linkWebImages')->name('admin.players.link_web_images');
	Route::get('/admin/jugadores/acciones/desenlazar_imagenes', 'PlayerController@unlinkWebImages')->name('admin.players.unlink_web_images');

	//Table Zones
	Route::get('/admin/marcado_de_zonas', 'TableZoneController@index')->name('admin.table_zones');
	Route::get('/admin/marcado_de_zonas/nuevo', 'TableZoneController@add')->name('admin.table_zones.add');
	Route::post('/admin/marcado_de_zonas/nuevo', 'TableZoneController@save')->name('admin.table_zones.save');
	Route::get('/admin/marcado_de_zonas/{id}', 'TableZoneController@edit')->name('admin.table_zones.edit');
	Route::put('/admin/marcado_de_zonas/{id}', 'TableZoneController@update')->name('admin.table_zones.update');
	Route::delete('/admin/marcado_de_zonas/eliminar/{id}', 'TableZoneController@destroy')->name('admin.table_zones.destroy');
	Route::get('/admin/marcado_de_zonas/eliminar-seleccionados/{ids}', 'TableZoneController@destroyMany')->name('admin.table_zones.destroy.many');
	Route::get('/admin/marcado_de_zonas/ver/{id}', 'TableZoneController@view')->name('admin.table_zones.view');
	Route::get('/admin/marcado_de_zonas/duplicar/{id}', 'TableZoneController@duplicate')->name('admin.table_zones.duplicate');
	Route::get('/admin/marcado_de_zonas/duplicar-seleccionados/{ids}', 'TableZoneController@duplicateMany')->name('admin.table_zones.duplicate.many');
	Route::get('/admin/marcado_de_zonas/exportar/{filename}/{type}/{filterName}/{order}/{ids?}', 'TableZoneController@exportFile')->name('admin.table_zones.export.file');
	Route::post('/admin/marcado_de_zonas/importar', 'TableZoneController@importFile')->name('admin.table_zones.import.file');

	// Seasons
	Route::get('/admin/temporadas', 'SeasonController@index')->name('admin.seasons');
	Route::get('/admin/temporadas/nueva', 'SeasonController@add')->name('admin.seasons.add');
	Route::post('/admin/temporadas/nueva', 'SeasonController@save')->name('admin.seasons.save');
	Route::get('/admin/temporadas/{id}', 'SeasonController@edit')->name('admin.seasons.edit');
	Route::put('/admin/temporadas/{id}', 'SeasonController@update')->name('admin.seasons.update');
	Route::delete('/admin/temporadas/eliminar/{id}', 'SeasonController@destroy')->name('admin.seasons.destroy');
	Route::get('/admin/temporadas/eliminar-seleccionados/{ids}', 'SeasonController@destroyMany')->name('admin.seasons.destroy.many');
	Route::get('/admin/temporadas/duplicar/{id}', 'SeasonController@duplicate')->name('admin.seasons.duplicate');
	Route::post('/admin/temporadas/duplicar/{id}/guardar', 'SeasonController@duplicateSave')->name('admin.seasons.duplicate.save');
	// Route::get('/admin/temporadas/duplicar-seleccionados/{ids}', 'SeasonController@duplicateMany')->name('admin.seasons.duplicate.many');
	Route::get('/admin/temporadas/activar-periodo-edicion-salarios/{id}', 'SeasonController@salariesPeriodActivate')->name('admin.seasons.salaries.activate');
	Route::get('/admin/temporadas/desactivar-periodo-edicion-salarios/{id}', 'SeasonController@salariesPeriodDesactivate')->name('admin.seasons.salaries.desactivate');
	Route::get('/admin/temporadas/activar-periodo-negociaciones/{id}', 'SeasonController@transfersPeriodActivate')->name('admin.seasons.transfers.activate');
	Route::get('/admin/temporadas/desactivar-periodo-negociaciones/{id}', 'SeasonController@transfersPeriodDesactivate')->name('admin.seasons.transfers.desactivate');
	Route::get('/admin/temporadas/activar-periodo-jugadores-libres/{id}', 'SeasonController@freePeriodActivate')->name('admin.seasons.free.activate');
	Route::get('/admin/temporadas/desactivar-periodo-jugadores-libres/{id}', 'SeasonController@freePeriodDesactivate')->name('admin.seasons.free.desactivate');
	Route::get('/admin/temporadas/activar-periodo-pago-clausulas/{id}', 'SeasonController@clausulesPeriodActivate')->name('admin.seasons.clausules.activate');
	Route::get('/admin/temporadas/desactivar-periodo-pago-clausulas/{id}', 'SeasonController@clausulesPeriodDesactivate')->name('admin.seasons.clausules.desactivate');

	Route::get('/admin/temporadas/exportar/{filename}/{type}/{filterName}/{order}/{ids?}', 'SeasonController@exportFile')->name('admin.seasons.export.file');
	Route::post('/admin/temporadas/importar', 'SeasonController@importFile')->name('admin.seasons.import.file');
	Route::get('/admin/temporadas/marcar-temporada-activa/{id}', 'SeasonController@setActiveSeason')->name('admin.seasons.setActiveSeason');

	Route::middleware('check_seasons')->group(function () {
		// Season Participants
		Route::get('/admin/participantes', 'SeasonParticipantController@index')->name('admin.season_participants');
		Route::get('/admin/participantes/{season_id}/nuevo', 'SeasonParticipantController@add')->name('admin.season_participants.add');
		Route::post('/admin/participantes/nuevo', 'SeasonParticipantController@save')->name('admin.season_participants.save');
		Route::get('/admin/participantes/{id}', 'SeasonParticipantController@edit')->name('admin.season_participants.edit');
		Route::put('/admin/participantes/{id}', 'SeasonParticipantController@update')->name('admin.season_participants.update');
		Route::get('/admin/participantes/expulsar/{id}', 'SeasonParticipantController@kickout')->name('admin.season_participants.kickout');
		Route::delete('/admin/participantes/eliminar/{id}', 'SeasonParticipantController@destroy')->name('admin.season_participants.destroy');
		Route::get('/admin/participantes/eliminar-seleccionados/{ids}', 'SeasonParticipantController@destroyMany')->name('admin.season_participants.destroy.many');
		Route::get('/admin/participantes/exportar/{filename}/{type}/{filterSeason}/{order}/{ids?}', 'SeasonParticipantController@exportFile')->name('admin.season_participants.export.file');
		Route::post('/admin/participantes/importar', 'SeasonParticipantController@importFile')->name('admin.season_participants.import.file');
		Route::get('/admin/participantes/historial-de-economia/{id}', 'SeasonParticipantController@cashHistory')->name('admin.season_participants.cash.history');
		Route::get('/admin/participantes/plantilla/{id}', 'SeasonParticipantController@roster')->name('admin.season_participants.roster');

		// Season Players
		Route::get('/admin/temporada-jugadores', 'SeasonPlayerController@index')->name('admin.season_players');

		Route::get('/admin/temporada-jugadores/jugadores/{season_id}/nuevo', 'SeasonPlayerController@add')->name('admin.season_players.add');
		Route::post('/admin/temporada-jugadores/jugadores/nuevo', 'SeasonPlayerController@save')->name('admin.season_players.save');
		Route::get('/admin/temporada-jugadores/jugadores/{id}', 'SeasonPlayerController@edit')->name('admin.season_players.edit');
		Route::put('/admin/temporada-jugadores/jugadores/{id}', 'SeasonPlayerController@update')->name('admin.season_players.update');
		Route::delete('/admin/temporada-jugadores/jugadores/eliminar/{id}', 'SeasonPlayerController@destroy')->name('admin.season_players.destroy');
		Route::get('/admin/temporada-jugadores/jugadores/eliminar-seleccionados/{ids}', 'SeasonPlayerController@destroyMany')->name('admin.season_players.destroy.many');
		// Route::get('/admin/temporada-jugadores/jugadores/ver/{id}', 'SeasonPlayerController@view')->name('admin.season_players.view');
		Route::get('/admin/temporada-jugadores/jugadores/activar/{id}', 'SeasonPlayerController@activate')->name('admin.season_players.activate');
		Route::get('/admin/temporada-jugadores/jugadores/activar-seleccionados/{ids}', 'SeasonPlayerController@activateMany')->name('admin.season_players.activate.many');
		Route::get('/admin/temporada-jugadores/jugadores/desactivar/{id}', 'SeasonPlayerController@desactivate')->name('admin.season_players.desactivate');
		Route::get('/admin/temporada-jugadores/jugadores/desactivar-seleccionados/{ids}', 'SeasonPlayerController@desactivateMany')->name('admin.season_players.desactivate.many');

		Route::get('/admin/temporada-jugadores/jugadores/resetear/{season_id}', 'SeasonPlayerController@reset')->name('admin.season_players.reset');
		Route::get('/admin/temporada-jugadores/jugadores/resetear-seleccionados/{ids}', 'SeasonPlayerController@resetMany')->name('admin.season_players.reset.many');

		Route::get('/admin/temporada-jugadores/jugadores/asignar-seleccionados/{ids}/{participant_id}', 'SeasonPlayerController@transferMany')->name('admin.season_players.transfer.many');
	Route::get('/admin/temporada-jugadores/jugadores/asignar-seleccionados-a-pack/{ids}/{pack_id}', 'SeasonPlayerController@transferPackMany')->name('admin.season_players.transferPack.many');

		Route::get('/admin/temporada-jugadores/jugadores/acciones/activar/{season_id}', 'SeasonPlayerController@activeAllPlayers')->name('admin.players.active.all.players');
		Route::get('/admin/temporada-jugadores/jugadores/acciones/desactivar/{season_id}', 'SeasonPlayerController@desactiveAllPlayers')->name('admin.players.desactive.all.players');

		Route::get('/admin/temporada-jugadores/jugadores/exportar/{filename}/{type}/{filterSeason}/{order}/{ids?}', 'SeasonPlayerController@exportFile')->name('admin.season_players.export.file');
		Route::post('/admin/temporada-jugadores/jugadores/importar', 'SeasonPlayerController@importFile')->name('admin.season_players.import.file');

		Route::get('/admin/temporada-jugadores/jugadores/generar-packs/{season_id}', 'SeasonPlayerController@generatePacks')->name('admin.season_players.generate.packs');

		// Cash History
		Route::get('/admin/historial-de-economia', 'SeasonParticipantCashHistoryController@index')->name('admin.season_cash_history');
		Route::get('/admin/historial-de-economia/{season_id}/nuevo', 'SeasonParticipantCashHistoryController@add')->name('admin.season_cash_history.add');
		Route::post('/admin/historial-de-economia/nuevo', 'SeasonParticipantCashHistoryController@save')->name('admin.season_cash_history.save');
		Route::get('/admin/historial-de-economia/{id}', 'SeasonParticipantCashHistoryController@edit')->name('admin.season_cash_history.edit');
		Route::put('/admin/historial-de-economia/{id}', 'SeasonParticipantCashHistoryController@update')->name('admin.season_cash_history.update');
		Route::delete('/admin/historial-de-economia/eliminar/{id}', 'SeasonParticipantCashHistoryController@destroy')->name('admin.season_cash_history.destroy');
		Route::get('/admin/historial-de-economia/eliminar-seleccionados/{ids}', 'SeasonParticipantCashHistoryController@destroyMany')->name('admin.season_cash_history.destroy.many');
		Route::get('/admin/historial-de-economia/exportar/{filename}/{type}/{filterParticipant}/{order}/{ids?}', 'SeasonParticipantCashHistoryController@exportFile')->name('admin.season_cash_history.export.file');
		Route::post('/admin/historial-de-economia/importar', 'SeasonParticipantCashHistoryController@importFile')->name('admin.season_cash_history.import.file');
		Route::get('/admin/historial-de-economia/{season_id}/pagar-salarios', 'SeasonParticipantCashHistoryController@pay_salaries')->name('admin.season_cash_history.pay_salaries');

		// Season Competitions
		Route::get('/admin/competiciones', 'SeasonCompetitionController@index')->name('admin.season_competitions');
		Route::get('/admin/competiciones/{season_id}/nuevo', 'SeasonCompetitionController@add')->name('admin.season_competitions.add');
		Route::post('/admin/competiciones/nueva', 'SeasonCompetitionController@save')->name('admin.season_competitions.save');
		Route::get('/admin/competiciones/{id}', 'SeasonCompetitionController@edit')->name('admin.season_competitions.edit');
		Route::put('/admin/competiciones/{id}', 'SeasonCompetitionController@update')->name('admin.season_competitions.update');
		Route::delete('/admin/competiciones/eliminar/{id}', 'SeasonCompetitionController@destroy')->name('admin.season_competitions.destroy');
		Route::get('/admin/competiciones/eliminar-seleccionados/{ids}', 'SeasonCompetitionController@destroyMany')->name('admin.season_competitions.destroy.many');
		Route::get('/admin/competiciones/duplicar/{id}', 'SeasonCompetitionController@duplicate')->name('admin.season_competitions.duplicate');
		Route::get('/admin/competiciones/duplicar-seleccionados/{ids}', 'SeasonCompetitionController@duplicateMany')->name('admin.season_competitions.duplicate.many');
		Route::get('/admin/competiciones/exportar/{filename}/{type}/{filterSeason}/{order}/{ids?}', 'SeasonCompetitionController@exportFile')->name('admin.season_competitions.export.file');
		Route::post('/admin/competiciones/importar', 'SeasonCompetitionController@importFile')->name('admin.season_competitions.import.file');

		// Season Competitions Phases
		Route::get('/admin/competiciones/{slug}/fases', 'SeasonCompetitionPhaseController@index')->name('admin.season_competitions_phases');
		Route::get('/admin/competiciones/{slug}/fases/nueva', 'SeasonCompetitionPhaseController@add')->name('admin.season_competitions_phases.add');
		Route::post('/admin/competiciones/{slug}/fases/nueva', 'SeasonCompetitionPhaseController@save')->name('admin.season_competitions_phases.save');
		Route::get('/admin/competiciones/{slug}/fases/{id}', 'SeasonCompetitionPhaseController@edit')->name('admin.season_competitions_phases.edit');
		Route::put('/admin/competiciones/{slug}/fases/{id}', 'SeasonCompetitionPhaseController@update')->name('admin.season_competitions_phases.update');
		Route::delete('/admin/competiciones/{slug}/fases/eliminar/{id}', 'SeasonCompetitionPhaseController@destroy')->name('admin.season_competitions_phases.destroy');
		Route::get('/admin/competiciones/{slug}/fases/eliminar-seleccionados/{ids}', 'SeasonCompetitionPhaseController@destroyMany')->name('admin.season_competitions_phases.destroy.many');
		Route::get('/admin/competiciones/{slug}/fases/acciones/activar/{id}', 'SeasonCompetitionPhaseController@activate')->name('admin.season_competitions_phases.activate');
		Route::get('/admin/competiciones/{slug}/fases/acciones/activar-seleccionados/{ids}', 'SeasonCompetitionPhaseController@activateMany')->name('admin.season_competitions_phases.activate.many');
		Route::get('/admin/competiciones/{slug}/fases/acciones/desactivar/{id}', 'SeasonCompetitionPhaseController@desactivate')->name('admin.season_competitions_phases.desactivate');
		Route::get('/admin/competiciones/{slug}/fases/acciones/desactivar-seleccionados/{ids}', 'SeasonCompetitionPhaseController@desactivateMany')->name('admin.season_competitions_phases.desactivate.many');
		Route::get('/admin/competiciones/{slug}/fases/exportar/{filename}/{type}/{ids?}', 'SeasonCompetitionPhaseController@exportFile')->name('admin.season_competitions_phases.export.file');
		Route::post('/admin/competiciones/{slug}/fases/importar', 'SeasonCompetitionPhaseController@importFile')->name('admin.season_competitions_phases.import.file');

		// Season Competitions Phases Groups
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/grupos', 'SeasonCompetitionPhaseGroupController@index')->name('admin.season_competitions_phases_groups');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/grupos/nuevo', 'SeasonCompetitionPhaseGroupController@add')->name('admin.season_competitions_phases_groups.add');
		Route::post('/admin/competiciones/{competition_slug}/{phase_slug}/grupos/nuevo', 'SeasonCompetitionPhaseGroupController@save')->name('admin.season_competitions_phases_groups.save');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/grupos/{id}', 'SeasonCompetitionPhaseGroupController@edit')->name('admin.season_competitions_phases_groups.edit');
		Route::put('/admin/competiciones/{competition_slug}/{phase_slug}/grupos/{id}', 'SeasonCompetitionPhaseGroupController@update')->name('admin.season_competitions_phases_groups.update');
		Route::delete('/admin/competiciones/{competition_slug}/{phase_slug}/grupos/eliminar/{id}', 'SeasonCompetitionPhaseGroupController@destroy')->name('admin.season_competitions_phases_groups.destroy');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/grupos/eliminar-seleccionados/{ids}', 'SeasonCompetitionPhaseGroupController@destroyMany')->name('admin.season_competitions_phases_groups.destroy.many');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/grupos/exportar/{filename}/{type}/{ids?}', 'SeasonCompetitionPhaseGroupController@exportFile')->name('admin.season_competitions_phases_groups.export.file');
		Route::post('/admin/competiciones/{competition_slug}/{phase_slug}/grupos/importar', 'SeasonCompetitionPhaseGroupController@importFile')->name('admin.season_competitions_phases_groups.import.file');

		// Season Competitions Phases Groups Participants
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/participantes', 'SeasonCompetitionPhaseGroupParticipantController@index')->name('admin.season_competitions_phases_groups_participants');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/participantes/nuevo', 'SeasonCompetitionPhaseGroupParticipantController@add')->name('admin.season_competitions_phases_groups_participants.add');
		Route::post('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/participantes/nuevo', 'SeasonCompetitionPhaseGroupParticipantController@save')->name('admin.season_competitions_phases_groups_participants.save');
		Route::delete('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/participantes/eliminar/{id}', 'SeasonCompetitionPhaseGroupParticipantController@destroy')->name('admin.season_competitions_phases_groups_participants.destroy');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/participantes/eliminar-seleccionados/{ids}', 'SeasonCompetitionPhaseGroupParticipantController@destroyMany')->name('admin.season_competitions_phases_groups_participants.destroy.many');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/participantes/sortear-participantes', 'SeasonCompetitionPhaseGroupParticipantController@raffle')->name('admin.season_competitions_phases_groups_participants.raffle');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/participantes/exportar/{filename}/{type}/{ids?}', 'SeasonCompetitionPhaseGroupParticipantController@exportFile')->name('admin.season_competitions_phases_groups_participants.export.file');
		Route::post('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/participantes/importar', 'SeasonCompetitionPhaseGroupParticipantController@importFile')->name('admin.season_competitions_phases_groups_participants.import.file');

		// Season Competitions Phases Groups Leagues
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/liga', 'SeasonCompetitionPhaseGroupLeagueController@index')->name('admin.season_competitions_phases_groups_leagues');
		Route::put('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/liga/{id}', 'SeasonCompetitionPhaseGroupLeagueController@save')->name('admin.season_competitions_phases_groups_leagues.save');
			// calendar view
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/liga/calendario', 'SeasonCompetitionPhaseGroupLeagueController@calendar')->name('admin.season_competitions_phases_groups_leagues.calendar');
			// calendar generate days & matches
		Route::post('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/liga/calendario/generar', 'SeasonCompetitionPhaseGroupLeagueController@calendar_generate')->name('admin.season_competitions_phases_groups_leagues.calendar.generate');
			// day - activate & desactivate
		Route::get('/admin/competiciones/activar-jornada/{day_id}', 'SeasonCompetitionPhaseGroupLeagueController@activate_day')->name('admin.season_competitions_phases_groups_leagues.calendar.day.activate');
		Route::get('/admin/competiciones/desactivar-jornada/{day_id}', 'SeasonCompetitionPhaseGroupLeagueController@desactivate_day')->name('admin.season_competitions_phases_groups_leagues.calendar.day.desactivate');
			// table view
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/liga/clasificacion', 'SeasonCompetitionPhaseGroupLeagueController@table')->name('admin.season_competitions_phases_groups_leagues.table');
			// edit match - result and stadistics
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/liga/calendario/partido/{id}', 'SeasonCompetitionPhaseGroupLeagueController@editMatch')->name('admin.season_competitions_phases_groups_leagues.edit_match');
		Route::put('/admin/competiciones/actualizar-resultado-partido/{match_id}', 'SeasonCompetitionPhaseGroupLeagueController@updateMatch')->name('admin.season_competitions_phases_groups_leagues.update_match');
			// edit match - only stadistics
		Route::get('/admin/competiciones/editar-estadisticas-partido/{match_id}', 'SeasonCompetitionPhaseGroupLeagueController@editMatchStats')->name('admin.season_competitions_phases_groups_leagues.edit_match_stats');
		Route::put('/admin/competiciones/actualizar-estadisticas-partido/{match_id}', 'SeasonCompetitionPhaseGroupLeagueController@updateMatchStats')->name('admin.season_competitions_phases_groups_leagues.update_match_stats');
			// edit day - date_limit
		Route::get('/admin/competiciones/editar-fecha-limite-jornada/{day_id}', 'SeasonCompetitionPhaseGroupLeagueController@edit_day_limit')->name('admin.season_competitions_phases_groups_leagues.calendar.day.edit_limit');
		Route::put('/admin/competiciones/actualizar-fecha-limite-jornada/{day_id}', 'SeasonCompetitionPhaseGroupLeagueController@update_day_limit')->name('admin.season_competitions_phases_groups_leagues.calendar.day.update_limit');
			// edit match - date_limit
		Route::get('/admin/competiciones/editar-fecha-limite-partido/{match_id}', 'SeasonCompetitionPhaseGroupLeagueController@edit_match_limit')->name('admin.season_competitions_phases_groups_leagues.calendar.match.edit_limit');
		Route::put('/admin/competiciones/actualizar-fecha-limite-partido/{match_id}', 'SeasonCompetitionPhaseGroupLeagueController@update_match_limit')->name('admin.season_competitions_phases_groups_leagues.calendar.match.update_limit');
			// reset match
		Route::get('/admin/competiciones/resetear-partido/{id}', 'SeasonCompetitionPhaseGroupLeagueController@resetMatch')->name('admin.season_competitions_phases_groups_leagues.reset_match');
			// stadistics view
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/liga/estadisticas', 'SeasonCompetitionPhaseGroupLeagueController@stats')->name('admin.season_competitions_phases_groups_leagues.stats');

		// Season Competitions Phases Groups PlayOffs
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/playoffs', 'PlayOffController@index')->name('admin.season_competitions_phases_groups_playoffs');
			// update playoff configuration
		Route::put('/admin/competiciones/playoffs/actualizar-configuración/{id}', 'PlayOffController@update')->name('admin.season_competitions_phases_groups_playoffs.update');
			// update round configuration
		Route::put('/admin/competiciones/playoffs/actualizar-configuración-ronda/{round_id}', 'PlayOffController@roundUpdate')->name('admin.season_competitions_phases_groups_playoffs.round.update');
			// generate rounds
		Route::get('/admin/competiciones/playoffs/generar-rondas/{playoff_id}/{num_rounds}', 'PlayOffController@generate_rounds')->name('admin.season_competitions_phases_groups_playoffs.generate_rounds');
			// reset rounds
		Route::get('/admin/competiciones/playoffs/resetear-rondas/{playoff_id}', 'PlayOffController@reset_rounds')->name('admin.season_competitions_phases_groups_playoffs.reset_rounds');
			// generate clashes
		Route::get('/admin/competiciones/playoffs/sortear-emparejamientos-vacio-ronda/{round_id}', 'PlayOffController@generate_empty_clashes')->name('admin.season_competitions_phases_groups_playoffs.generate_empty_clashes');
		Route::get('/admin/competiciones/playoffs/sortear-emparejamientos-ronda/{round_id}', 'PlayOffController@generate_clashes')->name('admin.season_competitions_phases_groups_playoffs.generate_clashes');
			// restore clashes
		Route::get('/admin/competiciones/playoffs/restaurar-emparejamientos-ronda/{round_id}', 'PlayOffController@restore_clashes')->name('admin.season_competitions_phases_groups_playoffs.restore_clashes');
			// assing participants in clash
		Route::get('/admin/competiciones/playoffs/asignar-participante-local-al-emparejamiento/{clash_id}', 'PlayOffController@assing_local_participant_in_clash')->name('admin.season_competitions_phases_groups_playoffs.clashes.assing_local_participant');
		Route::put('/admin/competiciones/playoffs/asignar-participante-local-al-emparejamiento/{clash_id}/actualizar', 'PlayOffController@update_assing_local_participant_in_clash')->name('admin.season_competitions_phases_groups_playoffs.clashes.assing_local_participant.update');
		Route::get('/admin/competiciones/playoffs/asignar-participante-visitante-al-emparejamiento/{clash_id}', 'PlayOffController@assing_visitor_participant_in_clash')->name('admin.season_competitions_phases_groups_playoffs.clashes.assing_visitor_participant');
		Route::put('/admin/competiciones/playoffs/asignar-participante-visitante-al-emparejamiento/{clash_id}/actualizar', 'PlayOffController@update_assing_visitor_participant_in_clash')->name('admin.season_competitions_phases_groups_playoffs.clashes.assing_visitor_participant.update');
		Route::get('/admin/competiciones/playoffs/liberar-participante-local-al-emparejamiento/{clash_id}', 'PlayOffController@liberate_local_participant_in_clash')->name('admin.season_competitions_phases_groups_playoffs.clashes.liberate_local_participant');
		Route::get('/admin/competiciones/playoffs/liberar-participante-visitante-al-emparejamiento/{clash_id}', 'PlayOffController@liberate_visitor_participant_in_clash')->name('admin.season_competitions_phases_groups_playoffs.clashes.liberate_visitor_participant');
			// edit match
		Route::get('admin/competiciones/playoffs/edita-partido/{match_id}', 'PlayOffController@editMatch')->name('admin.season_competitions_phases_groups_playoffs.clashes.match.edit');
			// update match
		Route::put('admin/competiciones/playoffs/editar-partido/{match_id}', 'PlayOffController@updateMatch')->name('admin.season_competitions_phases_groups_playoffs.clashes.match.update');



		Route::put('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/playoffs/{id}', 'PlayOffController@save')->name('admin.season_competitions_phases_groups_playoffs.save');

		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/playoffs/rondas', 'PlayOffController@rounds')->name('admin.season_competitions_phases_groups_playoffs.rounds');


		Route::post('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/playoffs/calendario/generar', 'PlayOffController@calendar_generate')->name('admin.season_competitions_phases_groups_playoffs.calendar.generate');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/playoffs/clasificacion', 'PlayOffController@table')->name('admin.season_competitions_phases_groups_playoffs.table');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/playoffs/calendario/partido/{id}', 'PlayOffController@editMatch')->name('admin.season_competitions_phases_groups_playoffs.edit_match');
		Route::put('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/playoffs/calendario/partido/{id}', 'PlayOffController@updateMatch')->name('admin.season_competitions_phases_groups_playoffs.update_match');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/playoffs/calendario/partido/{id}/reset', 'PlayOffController@resetMatch')->name('admin.season_competitions_phases_groups_playoffs.reset_match');
		Route::get('/admin/competiciones/{competition_slug}/{phase_slug}/{group_slug}/playoffs/estadisticas', 'PlayOffController@stats')->name('admin.season_competitions_phases_groups_playoffs.stats');

	});
});
