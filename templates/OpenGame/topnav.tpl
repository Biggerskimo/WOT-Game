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
				</td>
			</tr>
		</table>
	</div>
</center>