<center>
	<table>
		<tr>
			<td>
				Folgende Flotten standen sich {@TIME_NOW+3600|time:"am %d.%m.%Y um %H:%M:%S"} gegen&uuml;ber:
				<br>
				{foreach from=$roundData key='roundNo' item='data'}
					{assign var='attackerShotCount' value=0}
					{assign var='attackerWeaponCount' value=0}
					{assign var='attackerImbibedCount' value=0}
					{assign var='defenderShotCount' value=0}
					{assign var='defenderWeaponCount' value=0}
					{assign var='defenderImbibedCount' value=0}
					<table border="1" width="100%">
						<tr>
							{foreach from=$data.attackerData key='fleetID' item='fleet'}
								{assign var='fleetObj' value=$navalFormationFleets.$fleetID}
								{assign var='userID' value=$fleetObj->fleet_owner}
								{assign var='userObj' value=$users.$userID}

								{assign var='shipNames' value=''}
								{assign var='shipCounts' value=''}
								{assign var='shipWeapons' value=''}
								{assign var='shipShields' value=''}
								{assign var='shipHullPlatings' value=''}

								{* loop through ship types *}
								{foreach from=$fleet key='shipTypeID' item='shipArray'}
									{if $shipArray.count > 0}
										{assign var='shipCount' value=$shipArray.count|number_format:0:',':'.'}
										{assign var='shipWeapon' value=$shipArray.weapon|number_format:0:',':'.'}
										{assign var='shipShield' value=$shipArray.shield|number_format:0:',':'.'}
										{assign var='shipHullPlating' value=$shipArray.origHullPlating|number_format:0:',':'.'}

										{assign var='shipNames' value=$shipNames|concat:'<th>':$shipTypeNames.$shipTypeID:'</th>'}
										{assign var='shipCounts' value=$shipCounts|concat:'<th>':$shipCount:'</th>'}
										{assign var='shipWeapons' value=$shipWeapons|concat:'<th>':$shipWeapon:'</th>'}
										{assign var='shipShields' value=$shipShields|concat:'<th>':$shipShield:'</th>'}
										{assign var='shipHullPlatings' value=$shipHullPlatings|concat:'<th>':$shipHullPlating:'</th>'}
									{/if}
								{/foreach}

								<th>
									<br />
									<center>
										Angreifer #{#$fleetID}
										<br>
										Waffen: {$fleetObj->weaponTech*10}% Schilde: {$fleetObj->shieldTech*10}% H&uuml;lle: {$fleetObj->hullPlatingTech*10}%
										<table border="1">
											{if !$shipNames|empty}
												<tr>
													<th>
														Typ
													</th>
													{@$shipNames}
												</tr>
												<tr>
													<th>
														Anz.
													</th>
													{@$shipCounts}
												</tr>
												<tr>
													<th>
														Waffen
													</th>
													{@$shipWeapons}
												</tr>
												<tr>
													<th>
														Schilde
													</th>
													{@$shipShields}
												</tr>
												<tr>
													<th>
														H&uuml;lle
													</th>
													{@$shipHullPlatings}
												</tr>
											{else}
												<br />
												Vernichtet.
											{/if}
										</table>
									</center>
								</th>
							{/foreach}
						</tr>
					</table>
					<table border="1" width="100%">
						<tr>
							{foreach from=$data.defenderData key='fleetID' item='fleet'}
								{assign var='fleetObj' value=$standByFleets.$fleetID}
								{assign var='userID' value=$fleetObj->fleet_owner}
								{assign var='userObj' value=$users.$userID}
								{assign var='userID' value=$defenderObj->userID}

								{assign var='shipNames' value=''}
								{assign var='shipCounts' value=''}
								{assign var='shipWeapons' value=''}
								{assign var='shipShields' value=''}
								{assign var='shipHullPlatings' value=''}

								{* loop through ship types *}
								{foreach from=$fleet key='shipTypeID' item='shipArray'}
									{if $shipArray.count > 0}
										{assign var='shipCount' value=$shipArray.count|number_format:0:',':'.'}
										{assign var='shipWeapon' value=$shipArray.weapon|number_format:0:',':'.'}
										{assign var='shipShield' value=$shipArray.shield|number_format:0:',':'.'}
										{assign var='shipHullPlating' value=$shipArray.origHullPlating|number_format:0:',':'.'}

										{assign var='shipNames' value=$shipNames|concat:'<th>':$shipTypeNames.$shipTypeID:'</th>'}
										{assign var='shipCounts' value=$shipCounts|concat:'<th>':$shipCount:'</th>'}
										{assign var='shipWeapons' value=$shipWeapons|concat:'<th>':$shipWeapon:'</th>'}
										{assign var='shipShields' value=$shipShields|concat:'<th>':$shipShield:'</th>'}
										{assign var='shipHullPlatings' value=$shipHullPlatings|concat:'<th>':$shipHullPlating:'</th>'}
									{/if}
								{/foreach}

								<th>
									<br />
									<center>
										<!-- ... -->
										Verteidiger #{#$fleetID}
										<br>
										Waffen: {$fleetObj->weaponTech*10}% Schilde: {$fleetObj->shieldTech*10}% H&uuml;lle: {$fleetObj->hullPlatingTech*10}%
										<table border="1">
											{if !$shipNames|empty}
												<tr>
													<th>
														Typ
													</th>
													{@$shipNames}
												</tr>
												<tr>
													<th>
														Anz.
													</th>
													{@$shipCounts}
												</tr>
												<tr>
													<th>
														Waffen
													</th>
													{@$shipWeapons}
												</tr>
												<tr>
													<th>
														Schilde
													</th>
													{@$shipShields}
												</tr>
												<tr>
													<th>
														H&uuml;lle
													</th>
													{@$shipHullPlatings}
												</tr>
											{else}
												<br />
												Vernichtet.
											{/if}
										</table>
									</center>
								</th>
							{/foreach}
						</tr>
					</table>
					{* round summary *}
					{assign var='nextRoundNo' value=$roundNo+1}
					{if $roundData.$nextRoundNo|isset}
						{assign var='nextData' value=$roundData.$nextRoundNo}
						{assign var='attackerShotCount' value=0}
						{assign var='attackerWeaponCount' value=0}
						{assign var='attackerImbibedCount' value=0}
						{assign var='defenderShotCount' value=0}
						{assign var='defenderWeaponCount' value=0}
						{assign var='defenderImbibedCount' value=0}

						{foreach from=$nextData.attackerData item='fleet'}
							{foreach from=$fleet key='shipTypeID' item='shipArray'}
								{assign var='attackerShotCount' value=$attackerShotCount+$shipArray.shots}
								{assign var='attackerImbibedCount' value=$attackerImbibedCount+$shipArray.imbibed}
								{assign var='attackerWeaponCount' value=$attackerWeaponCount+$shipArray.shots*$shipArray.weapon}
							{/foreach}
						{/foreach}
						{foreach from=$nextData.defenderData item='fleet'}
							{foreach from=$fleet key='shipTypeID' item='shipArray'}
								{assign var='defenderShotCount' value=$defenderShotCount+$shipArray.shots}
								{assign var='defenderImbibedCount' value=$defenderImbibedCount+$shipArray.imbibed}
								{assign var='defenderWeaponCount' value=$defenderWeaponCount+$shipArray.shots*$shipArray.weapon}
							{/foreach}
						{/foreach}


							Die angreifende Flotte schie&szlig;t insgesamt {$attackerShotCount|number_format:0:',':'.'} mal mit Gesamtst&auml;rke {$attackerWeaponCount|number_format:0:',':'.'} auf den Verteidiger. Die Schilde des Verteidigers absorbieren {$defenderImbibedCount|number_format:0:',':'.'} Schadenspunkte.
							<br />
							Die verteidigende Flotte schie&szlig;t insgesamt {$defenderShotCount|number_format:0:',':'.'} mal mit Gesamtst&auml;rke {$defenderWeaponCount|number_format:0:',':'.'} auf den Angreifer. Die Schilde des Angreifers absorbieren {$attackerImbibedCount|number_format:0:',':'.'} Schadenspunkte.
					{/if}
				{/foreach}
				<br />
				{if $winner == 'attacker'}
					Der Angreifer hat die Schlacht gewonnen!
					<br />
					{* list booty *}
					Er erbeutet
					<br />
					{$booty.metal|number_format:0:',':'.'} Metall, {$booty.crystal|number_format:0:',':'.'} Kristall und {$booty.deuterium|number_format:0:',':'.'} Deuterium.
				{else}
					{if $winner == 'defender'}
						Der Verteidiger hat die Schlacht gewonnen!
					{else} {* draw *}
						Die Schlacht endet unentschieden, beide Flotten ziehen sich auf ihre Heimatplaneten zur&uuml;ck.
					{/if}
				{/if}
				<br />
				<br />
				Der Angreifer hat insgesamt {$units.attacker|number_format:0:',':'.'} Units verloren.
				<br />
				Der Verteidiger hat insgesamt {$units.defender|number_format:0:',':'.'} Units verloren.
				<br />
				Auf diesen Raumkoordinaten liegen nun {$debris.metal|number_format:0:',':'.'} Metall und {$debris.crystal|number_format:0:',':'.'} Kristall.
				<br />
				<br />
				{*recreatedDefense}
				<br>*}
				{if $moon.chance !== 0}
					Die Chance einer Mondentstehung beträgt {$moon.chance}%.
					{if $moon.size !== null}
						<br />
						Die enormen Mengen an freiem Metall und Kristall ziehen sich an und formen einen Trabanten um den Planeten.
					{/if}
				{/if}
			</td>
		</tr>
	</table>
</table>