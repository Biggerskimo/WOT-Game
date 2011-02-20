{include file="documentHeader"}
	<head>
		<title>{lang}wot.payment.paypal.page.title{/lang}</title>
		<script type="text/javascript">
			language = { };
		</script>
		{include file="headInclude"}
	</head>
	<body>
		{include file="topnav"}
			<div class="main content payments">
			{if $success}
				<p class="success">
					Die Bestellung wurde offenbar erfolgreich durchgef&uuml;hrt. Dennoch kann es noch etwas dauern, bis wir von Paypal eine Best&auml;tigung &uuml;ber den Erfolg der Transaktion bekommen. Du bekommst dann alsbald dein Dilizium gutgeschrieben. Du erh&auml;lst zeitgleich auch eine Nachricht.
	Falls du innerhalb der n&auml;chsten Stunde keine Best&auml;tigung erhalten solltest, kannst du dich an einen Administrator wenden.
				</p>
			{/if}
			
			<div class="paymentOptions">
				<div class="contentDescriptor">
					<div>
						&nbsp;
					</div>
					<div class="prepayment">
						Vorkasse
					</div>
					<div class="paypal">
						Paypal
					</div>
				</div>
				
				{assign var='accountID36' value=$this->user->accountID|base_convert:10:36}
				
				{* abo *}
				<div class="contentRow paymentOption">
					<div class="contentDescriptor">
						<p>Abonnement</p>
						<p><span>Alle drei Monate 30.000 Dilizium</span><span>f&uuml;r 9,00 EUR</span></p>
					</div>
					<div class="prepayment">
						<p><span>Dauerauftrag</span> (viertelj&auml;hrlich)</p>
						<p>Verwendungszweck: <span>{$this->user->username} {$this->getConfig('serverName')} 30000 Dilizium, Nr. {@$this->user->accountID}1-{@$accountID36|strtoupper}A</span><p>
						<p>Betrag: <span>9,00 EUR</span></p>
					</div>
					<div class="paypal">
						<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_xclick-subscriptions" />
						<input type="hidden" name="business" value="bigmac_1297458714_biz@gmx.de" />
						
						<input type="hidden" name="item_name" value="30.000 Dilizium" />
						<input type="hidden" name="no_note" value="1" />
						<input type="hidden" name="no_shipping" value="1" />
						
						<input type="hidden" name="notify_url" value="{@$url}?action=PaypalIPN" />
						<input type="hidden" name="custom" value="userID={@$this->user->userID}|time={@TIME_NOW}|type=1" />
						<input type="hidden" name="return" value="{@$url}?page=Paypal&success=1" />
						<input type="hidden" name="cancel_return" value="{@$url}?page=Paypal" />
						
						<input type="hidden" name="currency_code" value="EUR" />
						<input type="hidden" name="a3" value="5.00" />
						<input type="hidden" name="p3" value="1" />
						<input type="hidden" name="t3" value="D" />
						<input type="hidden" name="src" value="1" />
						<input type="hidden" name="srt" value="0" />
						
						<input type="image" src="https://www.sandbox.paypal.com/de_DE/DE/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal." />
						<img alt="" border="0" src="https://www.sandbox.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
						</form>
					</div>
				</div>
				
				{* week-package *}
				<div class="contentRow paymentOption">
					<div class="contentDescriptor">
						<p>Wochenpaket</p>
						<p><span>Einmalig 2.500 Dilizium</span><span>f&uuml;r 1,50 EUR</span></p>
					</div>
					<div class="prepayment">
						<p>&Uuml;berweisung</p>
						<p>Verwendungszweck: <span>{$this->user->username} {$this->getConfig('serverName')} 2500 Dilizium, Nr. {@$this->user->accountID}2-{@$accountID36|strtoupper}B</span><p>
						<p>Betrag: <span>1,50 EUR</span></p>
					</div>
					<div class="paypal">
						<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_xclick" />
							<input type="hidden" name="business" value="bigmac_1297458714_biz@gmx.de" />
							
							<input type="hidden" name="item_name" value="2.500 Dilizium" />
							<input type="hidden" name="no_note" value="1" />
							<input type="hidden" name="no_shipping" value="1" />
							
							<input type="hidden" name="notify_url" value="{@$url}?action=PaypalIPN" />
							<input type="hidden" name="custom" value="userID={@$this->user->userID}|time={@TIME_NOW}|type=2" />
							<input type="hidden" name="return" value="{@$url}?page=Paypal&success=1" />
							<input type="hidden" name="cancel_return" value="{@$url}?page=Paypal" />
							
							<input type="hidden" name="currency_code" value="EUR" />
							<input type="hidden" name="amount" value="1.50" />
							<input type="hidden" name="quantity" value="1" />
							<input type="hidden" name="undefined_quantity" value="1" />
							
							<input type="image" src="https://www.sandbox.paypal.com/de_DE/DE/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal." />
							<img alt="" border="0" src="https://www.sandbox.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
						</form>
					</div>
				</div>
				
				{* month-package *}
				<div class="contentRow paymentOption">
					<div class="contentDescriptor">
						<p>Monatspaket</p>
						<p><span>Einmalig 10.000 Dilizium</span><span>f&uuml;r 4,00 EUR</span></p>
					</div>
					<div class="prepayment">
						<p>&Uuml;berweisung</p>
						<p>Verwendungszweck: <span>{$this->user->username} {$this->getConfig('serverName')} 10000 Dilizium, Nr. {@$this->user->accountID}3-{@$accountID36|strtoupper}C</span><p>
						<p>Betrag: <span>4,00 EUR</span></p>
					</div>
					<div class="paypal">
						<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_xclick" />
							<input type="hidden" name="business" value="bigmac_1297458714_biz@gmx.de" />
							
							<input type="hidden" name="item_name" value="10.000 Dilizium" />
							<input type="hidden" name="no_note" value="1" />
							<input type="hidden" name="no_shipping" value="1" />
							
							<input type="hidden" name="notify_url" value="{@$url}?action=PaypalIPN" />
							<input type="hidden" name="custom" value="userID={@$this->user->userID}|time={@TIME_NOW}|type=3" />
							<input type="hidden" name="return" value="{@$url}?page=Paypal&success=1" />
							<input type="hidden" name="cancel_return" value="{@$url}?page=Paypal" />
							
							<input type="hidden" name="currency_code" value="EUR" />
							<input type="hidden" name="amount" value="4.00" />
							<input type="hidden" name="quantity" value="1" />
							<input type="hidden" name="undefined_quantity" value="1" />
							
							<input type="image" src="https://www.sandbox.paypal.com/de_DE/DE/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal." />
							<img alt="" border="0" src="https://www.sandbox.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
						</form>
					</div>
				</div>
				
				{* 3-month-package *}
				<div class="contentRow paymentOption">
					<div class="contentDescriptor">
						<p>Dreimonatspaket</p>
						<p><span>Einmalig 30.000 Dilizium</span><span>f&uuml;r 10,00 EUR</span></p>
					</div>
					<div class="prepayment">
						<p>&Uuml;berweisung</p>
						<p>Verwendungszweck: <span>{$this->user->username} {$this->getConfig('serverName')} 30000 Dilizium, Nr. {@$this->user->accountID}4-{@$accountID36|strtoupper}D</span><p>
						<p>Betrag: <span>10,00 EUR</span></p>
					</div>
					<div class="paypal">
						<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_xclick" />
							<input type="hidden" name="business" value="bigmac_1297458714_biz@gmx.de" />
							
							<input type="hidden" name="item_name" value="30.000 Dilizium" />
							<input type="hidden" name="no_note" value="1" />
							<input type="hidden" name="no_shipping" value="1" />
							
							<input type="hidden" name="notify_url" value="{@$url}?action=PaypalIPN" />
							<input type="hidden" name="custom" value="userID={@$this->user->userID}|time={@TIME_NOW}|type=4" />
							<input type="hidden" name="return" value="{@$url}?page=Paypal&success=1" />
							<input type="hidden" name="cancel_return" value="{@$url}?page=Paypal" />
							
							<input type="hidden" name="currency_code" value="EUR" />
							<input type="hidden" name="amount" value="10.00" />
							<input type="hidden" name="quantity" value="1" />
							<input type="hidden" name="undefined_quantity" value="1" />
							
							<input type="image" src="https://www.sandbox.paypal.com/de_DE/DE/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal." />
							<img alt="" border="0" src="https://www.sandbox.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
						</form>
					</div>
				</div>
			</div>
		</div>
		
		{include file='footer'}
	</body>
</html>