<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<form class="form-horizontal">
	<fieldset>
		<div class="form-group">
			<label class="col-lg-4 control-label">{{Fonctionnement du plugin}}</label>
			<div class="col-lg-5">
				Ce plugin utilise le site Enedis pour obtenir les informations de votre consommation depuis votre compteur Linky<br />
				Si vous n'avez pas encore de compte Enedis, vous pouvez l'ouvrir 
				<a href="https://espace-client-particuliers.enedis.fr/web/espace-particuliers/creation-de-compte" target="_blank" > en cliquant ici</a>.<br />
				Pour acceder aux donnees : creer une vue, un design pour charger le graphique, ou consulter l'historique.
				<br /><br />
				*Librement inspiré du module d'Edouard Marchand, sous license AGPL.
				<br /><br />
				Contributions : Bugdamon, glenan
			</div>
		</div>
	</fieldset>
</form>