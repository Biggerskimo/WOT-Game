<center>
	<div id="header_top">
		<table>
			<tr>
		  		<td>
		  		</td>
		  		<td>
					<center>
						<table>
							<tr>
								<td>
									<img src="{dpath}planeten/small/s_{image}.jpg" height="50" width="50">
								</td>
				  				<td>
				  					<select size="1" onChange="eval('location=\''+this.options[this.selectedIndex].value+'\'');">
										{planetlist}
									</select>
									<table border="1">
									</table>
								</td>
							</tr>
						</table>
					</center>
				</td>
				<td>
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td align="center">
							</td>
							<td align="center" width="85">
								<img src="{dpath}images/metall.gif" border="0" height="22" width="42">
							</td>
					  		<td align="center" width="85">
					  			<img src="{dpath}images/kristall.gif" border="0" height="22" width="42">
					  		</td>
							<td align="center" width="85">
								<img src="{dpath}images/deuterium.gif" border="0" height="22" width="42">
							</td>
							<td align="center" width="85">
								<img src="{dpath}images/energie.gif" border="0" height="22" width="42">
							</td>
							<td align="center">
							</td>
						</tr>
						<tr>
							<td align="center">
								<i>
									<b>
										&nbsp;&nbsp;
									</b>
								</i>
							</td>
							<td align="center" width="85">
								<i>
									<b style="color: rgb(173, 174, 173);">
										{Metal}
									</b>
								</i>
							</td>
							<td align="center" width="85">
								<i>
									<b style="color: rgb(239, 81, 239);">
										{Crystal}
									</b>
								</i>
							</td>
							<td align="center" width="85">
								<i>
									<b style="color: rgb(247, 117, 66);">
										{Deuterium}
									</b>
								</i>
							</td>
							<td align="center" width="85">
								<i>
									<b style="color: rgb(156, 113, 198);">
										{Energy}
									</b>
								</i>
							</td>
							<td align="center">
								<i>
									<b>
										&nbsp;&nbsp;
									</b>
								</i>
							</td>
						</tr>
						<tr>
							<td align="center">
							</td>
							<td align="center" width="85">
								{metal}
							</td>
		     				<td align="center" width="85">
		     					{crystal}
		     				</td>
							<td align="center" width="85">
								{deuterium}
							</td>
							<td align="center" width="85">
								{energy}
							</td>
							<td align="center">
							</td>
						</tr>
					</table>
				</td>
				<td>
					<table>
						<tr>
							<td>
								<a href="http://bgs.gdynamite.de/charts_vote_1371.html" target="_blank"><img src="http://voting.gdynamite.de/images/gd_animbutton.gif" border="0"></a>
							</td>
						</tr>
					  	<!--tr>
					  		<td>
					  			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
									<input type="hidden" name="cmd" value="_s-xclick">
									<input type="image" src="https://www.paypal.com/de_DE/i/btn/x-click-but21.gif" border="0" name="submit" alt="Zahlen Sie mit PayPal - schnell, kostenlos und sicher!">
									<img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
									<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
								</form>
					  		</td>
					  	</tr-->
					</table>
				</td>
			</tr>
		</table>
	</div>
</center>