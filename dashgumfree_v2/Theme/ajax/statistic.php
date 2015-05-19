<?php
session_start();
?>
<style type="text/css">
img.img-responsive { display: table-cell; width: 25%; }
</style>
<!--Start Breadcrumb-->
<div class="row">
	<div id="breadcrumb" class="col-xs-12">
		<a href="#" class="show-sidebar">
			<i class="fa fa-bars"></i>
		</a>
		<ol class="breadcrumb pull-left">
			<li><a href="index.html">Home</a></li>
			<li><a href="#">Dashboard</a></li>
		</ol>
		<div id="social" class="pull-right">
			<a href="#"><i class="fa fa-google-plus"></i></a>
			<a href="#"><i class="fa fa-facebook"></i></a>
			<a href="#"><i class="fa fa-twitter"></i></a>
			<a href="#"><i class="fa fa-linkedin"></i></a>
			<a href="#"><i class="fa fa-youtube"></i></a>
		</div>
	</div>
</div>
<!--End Breadcrumb-->
<!--Start Dashboard 1-->
<div id="dashboard-header" class="row">
	<div class="col-xs-2">
		<img class="img-responsive" src="img/statis.png">
	</div>
	<div class="col-xs-10 col-sm-4 col-md-5">
		<h1>Thống kê hệ thống</h1>
	</div>
</div>
<!--End Dashboard 1-->
<!--Start Dashboard 2-->
<div class="row-fluid">
	<div id="dashboard_links" class="col-xs-12 col-sm-2 pull-right">
		<ul class="nav nav-pills nav-stacked">
			<li class="active"><a href="#" class="tab-link" id="overview">Tổng quan</a></li>
			<li><a href="#" class="tab-link" id="clients">Nhân viên</a></li>
			<li><a href="#" class="tab-link" id="graph">Người dùng</a></li>
			<li><a href="#" class="tab-link" id="servers">Servers</a></li>
			<li><a href="#" class="tab-link" id="planning">Planning</a></li>
		</ul>
	</div>
	<div id="dashboard_tabs" class="col-xs-12 col-sm-10">
		<!--Start Dashboard Tab 1-->
		<div id="dashboard-overview" class="row" style="visibility: visible; position: relative;">
			<div id="ow-marketplace" class="col-sm-12 col-md-6">
				<div id="ow-setting">
					<a href="#"><i class="fa fa-folder-open"></i></a>
					<a href="#"><i class="fa fa-credit-card"></i></a>
					<a href="#"><i class="fa fa-ticket"></i></a>
					<a href="#"><i class="fa fa-bookmark-o"></i></a>
					<a href="#"><i class="fa fa-globe"></i></a>
				</div>
				<h4 class="page-header">MARKETPLACE</h4>
				<table id="ticker-table" class="table m-table table-bordered table-hover table-heading">
					<thead>
						<tr>
							<th>Ticker</th>
							<th>Price</th>
							<th>Change</th>
							<th>Weekly Chart</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="m-ticker"><b>BRDM</b><span>Broadem Inc.</span></td>
							<td class="m-price">33.27</td>
							<td class="m-change"><i class="fa fa-angle-up"></i> 1.45 (27&#37;)</td>
							<td class="td-graph"></td>
						</tr>
						<tr>
							<td class="m-ticker"><b>ASWLL</b><span>Aswell Corp.</span></td>
							<td class="m-price">45.13</td>
							<td class="m-change"><i class="fa fa-angle-up"></i> 6.32 (12&#37;)</td>
							<td class="td-graph"></td>
						</tr>
						<tr>
							<td class="m-ticker"><b>MIXL</b><span>Mixal LTD.</span></td>
							<td class="m-price">71.13</td>
							<td class="m-change"><i class="fa fa-angle-down"></i> 7.2 (12&#37;)</td>
							<td class="td-graph"></td>
						</tr>
						<tr>
							<td class="m-ticker"><b>LMPRD</b><span>L.A. Prod.</span></td>
							<td class="m-price">30.24</td>
							<td class="m-change"><i class="fa fa-angle-up"></i> 5.3 (18&#37;)</td>
							<td class="td-graph"></td>
						</tr>
						<tr>
							<td class="m-ticker"><b>ALK</b><span>Allien K.</span></td>
							<td class="m-price">51.1</td>
							<td class="m-change"><i class="fa fa-angle-up"></i> 7.5 (3.5&#37;)</td>
							<td class="td-graph"></td>
						</tr>
						<tr>
							<td class="m-ticker"><b>LNISW</b><span>Lenstri Sweet</span></td>
							<td class="m-price">123.12</td>
							<td class="m-change"><i class="fa fa-angle-down"></i> 54.3 (15.3&#37;)</td>
							<td class="td-graph"></td>
						</tr>
						<tr>
							<td class="m-ticker"><b>RNLD</b><span>Ron LEED</span></td>
							<td class="m-price">64.14</td>
							<td class="m-change"><i class="fa fa-angle-up"></i> 12.33 (0.3&#37;)</td>
							<td class="td-graph"></td>
						</tr>
						<tr>
							<td class="m-ticker"><b>BCN</b><span>BeetCN Corp.</span></td>
							<td class="m-price">64.14</td>
							<td class="m-change"><i class="fa fa-angle-up"></i> 12.33 (0.3&#37;)</td>
							<td class="td-graph"></td>
						</tr>
						<tr>
							<td class="m-ticker"><b>AWS</b><span>Awesome Inc.</span></td>
							<td class="m-price">64.14</td>
							<td class="m-change"><i class="fa fa-angle-up"></i> 12.33 (0.3&#37;)</td>
							<td class="td-graph"></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="col-xs-12 col-md-6">
				<div id="ow-donut" class="row">
					<div class="col-xs-4">
						<div id="morris_donut_1" style="width:120px;height:120px;"></div>
					</div>
					<div class="col-xs-4">
						<div id="morris_donut_2" style="width:120px;height:120px;"></div>
					</div>
					<div class="col-xs-4">
						<div id="morris_donut_3" style="width:120px;height:120px;"></div>
					</div>
				</div>
				<div id="ow-activity" class="row">
					<div class="col-xs-2 col-sm-1 col-md-2">
						<div class="v-txt">ACTIVITY</div>
					</div>
					<div class="col-xs-7 col-sm-5 col-md-6">
						<div class="row"><i class="fa fa-code"></i> Release published <span class="label label-default pull-right">01:17:34</span></div>
						<div class="row"><i class="fa fa-cloud-upload"></i> Backup created <span class="label label-default pull-right">03:23:34</span></div>
						<div class="row"><i class="fa fa-camera"></i> Snapshot created <span class="label label-default pull-right">04:22:11</span></div>
						<div class="row"><i class="fa fa fa-money"></i> Invoice pay <span class="label label-default pull-right">05:11:51</span></div>
						<div class="row"><i class="fa fa-briefcase"></i> Project edited <span class="label label-default pull-right">04:52:23</span></div>
						<div class="row"><i class="fa fa-floppy-o"></i> Project saved <span class="label label-default pull-right">07:11:01</span></div>
						<div class="row"><i class="fa fa-bug"></i> Bug fixed <span class="label label-default pull-right">09:10:31</span></div>
					</div>
					<div id="ow-stat" class="col-xs-3 col-sm-4 col-md-4 pull-right">
						<div class="row"><small><b>Ow Stat.:</b></small></div>
						<div class="row">&#37;user <sup>20,43</sup></div>
						<div class="row">&#37;nice <sup>1,01</sup></div>
						<div class="row">&#37;system <sup>27,34</sup></div>
						<div class="row">&#37;iowait <sup>2,02</sup></div>
						<div class="row">&#37;steal <sup>1,22</sup></div>
						<div class="row">&#37;idle <sup>47,98</sup></div>
						<div class="row">tps <sup>296546</sup></div>
					</div>
				</div>
				<div id="ow-summary" class="row">
					<div class="col-xs-12">
						<h4 class="page-header">&Sigma; SUMMARY</h4>
						<div class="row">
							<div class="col-xs-12">
								<div class="row">
									<div class="col-xs-6">Total commits<b>1245634</b></div>
									<div class="col-xs-6">Release count<b>227</b></div>
								</div>
								<div class="row">
									<div class="col-xs-6">Tests passed<b>5222345</b></div>
									<div class="col-xs-6">Tickets solved<b>324322</b></div>
								</div>
								<div class="row">
									<div class="col-xs-6">Active clients<b>52145</b></div>
									<div class="col-xs-6">Support team<b>288</b></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--End Dashboard Tab 1-->
		<!--Start Dashboard Tab 2-->
		<div id="dashboard-clients" class="row" style="width:100%; visibility: hidden; position: absolute;">
			<div class="col-xs-12">
				<h4 class="page-header">Thống kê nhân viên</h4>
				<div id="stat-staff" style="height: 300px;"></div>
			</div>
		</div>
		<!--End Dashboard Tab 2-->
		<!--Start Dashboard Tab 3-->
		<div id="dashboard-graph" class="row" style="width:100%; visibility: hidden; position: absolute;" >
			<div class="col-xs-12">
				<h4 class="page-header">Thống kê người dùng</h4>
				<div id="stat-user" style="height: 300px;"></div>
			</div>
		</div>
		<!--End Dashboard Tab 3-->
		<!--Start Dashboard Tab 4-->
		<div id="dashboard-servers" class="row" style="visibility: hidden; position: absolute;">
			<div class="col-xs-12 col-sm-6 col-md-4 ow-server">
				<h4 class="page-header text-right"><i class="fa fa-windows"></i>#SRV-APP</h4>
				<small>Application server</small>
				<div class="ow-settings">
					<a href="#"><i class="fa fa-gears"></i></a>
				</div>
				<div class="row ow-server-bottom">
					<div class="col-sm-4">
						<div class="knob-slider">
							<input id="knob-srv-1" class="knob" data-width="60"  data-height="60" data-angleOffset="180" data-fgColor="#6AA6D6" data-skin="tron" data-thickness=".2" value="">CPU Load
						</div>
					</div>
					<div class="col-sm-8">
						<div class="row"><i class="fa fa-windows"></i> Windows 2008</div>
						<div class="row"><i class="fa fa-user"></i> Active users - 49</div>
						<div class="row"><i class="fa fa-bolt"></i> Uptime - 10 days</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4 ow-server">
				<h4 class="page-header text-right"><i class="fa fa-windows"></i>#DB-MASTER</h4>
				<small>SQL server</small>
				<div class="ow-settings">
					<a href="#"><i class="fa fa-gears"></i></a>
				</div>
				<div class="row ow-server-bottom">
					<div class="col-sm-4">
						<div class="knob-slider">
							<input id="knob-srv-2" class="knob" data-width="60"  data-height="60" data-angleOffset="180" data-fgColor="#6AA6D6" data-skin="tron" data-thickness=".2" value="">CPU Load
						</div>
					</div>
					<div class="col-sm-8">
						<div class="row"><i class="fa fa-windows"></i> Windows 2013</div>
						<div class="row"><i class="fa fa-user"></i> Active users - 39</div>
						<div class="row"><i class="fa fa-bolt"></i> Uptime - 2 month 1 day</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4 ow-server">
				<h4 class="page-header text-right"><i class="fa fa-linux"></i>#DB-WEB</h4>
				<small>MySQL server</small>
				<div class="ow-settings">
					<a href="#"><i class="fa fa-gears"></i></a>
				</div>
				<div class="row ow-server-bottom">
					<div class="col-sm-4">
						<div class="knob-slider">
							<input id="knob-srv-3" class="knob" data-width="60"  data-height="60" data-angleOffset="180" data-fgColor="#6AA6D6" data-skin="tron" data-thickness=".2" value="">CPU Load
						</div>
					</div>
					<div class="col-sm-8">
						<div class="row"><i class="fa fa-linux"></i> CentOS 6.5</div>
						<div class="row"><i class="fa fa-user"></i> Active users - 298</div>
						<div class="row"><i class="fa fa-bolt"></i> Uptime - 9 month 17 day</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4 ow-server">
				<h4 class="page-header text-right"><i class="fa fa-linux"></i>#WWW-SRV</h4>
				<small>Web-server</small>
				<div class="ow-settings">
					<a href="#"><i class="fa fa-gears"></i></a>
				</div>
				<div class="row ow-server-bottom">
					<div class="col-sm-4">
						<div class="knob-slider">
							<input id="knob-srv-4" class="knob" data-width="60"  data-height="60" data-angleOffset="180" data-fgColor="#6AA6D6" data-skin="tron" data-thickness=".2" value="">CPU Load
						</div>
					</div>
					<div class="col-sm-8">
						<div class="row"><i class="fa fa-linux"></i> Centos 6.5</div>
						<div class="row"><i class="fa fa-user"></i> Active users - 1989</div>
						<div class="row"><i class="fa fa-bolt"></i> Uptime - 2 years 3 month</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4 ow-server">
				<h4 class="page-header text-right"><i class="fa fa-linux"></i>#PHONE-OFFICE</h4>
				<small>Asterisk</small>
				<div class="ow-settings">
					<a href="#"><i class="fa fa-gears"></i></a>
				</div>
				<div class="row ow-server-bottom">
					<div class="col-sm-4">
						<div class="knob-slider">
							<input id="knob-srv-5" class="knob" data-width="60"  data-height="60" data-angleOffset="180" data-fgColor="#6AA6D6" data-skin="tron" data-thickness=".2" value="">CPU Load
						</div>
					</div>
					<div class="col-sm-8">
						<div class="row"><i class="fa fa-linux"></i> Debian 6.4</div>
						<div class="row"><i class="fa fa-phone"></i> Active calls - 86</div>
						<div class="row"><i class="fa fa-bolt"></i> Uptime - 3 month 19 day</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4 ow-server">
				<h4 class="page-header text-right"><i class="fa fa-linux"></i>#DEVEL</h4>
				<small>DEV server</small>
				<div class="ow-settings">
					<a href="#"><i class="fa fa-gears"></i></a>
				</div>
				<div class="row ow-server-bottom">
					<div class="col-sm-4">
						<div class="knob-slider">
							<input id="knob-srv-6" class="knob" data-width="60"  data-height="60" data-angleOffset="180" data-fgColor="#6AA6D6" data-skin="tron" data-thickness=".2" value="">CPU Load
						</div>
					</div>
					<div class="col-sm-8">
						<div class="row"><i class="fa fa-linux"></i> CentOS 6.5</div>
						<div class="row"><i class="fa fa-archive"></i> Repositories - 17</div>
						<div class="row"><i class="fa fa-bolt"></i> Uptime - 4 month 21 day</div>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div id="ow-server-footer">
				<a href="#" class="col-xs-4 col-sm-2 btn-default text-center"><i class="fa fa-sun-o"></i> <b>287</b> <span>Hosts</span></a>
				<a href="#" class="col-xs-4 col-sm-2 btn-default text-center"><i class="fa fa-envelope-o"></i> <b>56</b> <span>Messages</span></a>
				<a href="#" class="col-xs-4 col-sm-2 btn-default text-center"><i class="fa fa-desktop"></i> <b>85</b> <span>Stations</span></a>
				<a href="#" class="col-xs-4 col-sm-2 btn-default text-center"><i class="fa fa-info-circle"></i> <b>33</b> <span>Errors</span></a>
				<a href="#" class="col-xs-4 col-sm-2 btn-default text-center"><i class="fa fa-comments-o"></i> <b>1386</b> <span>Comments</span></a>
				<a href="#" class="col-xs-4 col-sm-2 btn-default text-center"><i class="fa fa-user"></i> <b>19985</b> <span>Clients</span></a>
			</div>
		</div>
		<!--End Dashboard Tab 4-->
		<!--Start Dashboard Tab 5-->
		<div id="dashboard-planning" class="row" style="visibility: hidden; position: absolute;">
				<div class="col-xs-12 col-sm-6">
					<h4 class="page-header">Planned projects</h4>
					<a href="#">Expense items</a><a href="#" class="pull-right">Project members</a>
					<table class="table m-table table-bordered table-hover table-heading">
						<thead>
							<tr>
								<th>Projects</th>
								<th>Ending date</th>
								<th>Cost</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="m-ticker"><b>Network upgrade</b><span>Change Dlink devices to Cisco</span></td>
								<td class="m-price">Aug</td>
								<td class="m-change">179459</td>
							</tr>
							<tr>
								<td class="m-ticker"><b>Improved power equipment</b><span>Nevada datacenter</span></td>
								<td class="m-price">Nov</td>
								<td class="m-change">59411</td>
							</tr>
							<tr>
								<td class="m-ticker"><b>New ticket system</b><span>developed from scratch</span></td>
								<td class="m-price">Jul</td>
								<td class="m-change">14906</td>
							</tr>
							<tr>
								<td class="m-ticker"><b>Storage Area Network</b><span>project</span></td>
								<td class="m-price">Nov</td>
								<td class="m-change">250000</td>
							</tr>
							<tr>
								<td class="m-ticker"><b>New optical channels</b><span>6 links</span></td>
								<td class="m-price">Nov</td>
								<td class="m-change">22359</td>
							</tr>
							<tr>
								<td class="m-ticker"><b>Load-balance system</b><span>based on Linux</span></td>
								<td class="m-price">Dec</td>
								<td class="m-change">33950</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="col-xs-12 col-sm-6" id="planning-chart-1" style="height:250px;"><a href="#">Reports</a></div>
				<div class="col-xs-12 col-sm-8" id="planning-chart-2" style="height: 250px;"></div>
				<div class="col-xs-12 col-sm-4" id="planning-chart-3" style="height: 250px;"></div>
				<div class="col-xs-8">
					<h4 class="page-header">Quarterly forecast</h4>
					<div class="row">
						<div class="col-xs-3"><span>Q1</span>123,34234</div>
						<div class="col-xs-3"><span>Q2</span>123,34234</div>
						<div class="col-xs-3"><span>Q3</span>123,34234</div>
						<div class="col-xs-3"><span>Q4</span>123,34234</div>
					</div>
				</div>
				<div class="col-xs-4">
					<h4 class="page-header">Total forecast</h4>
					<div class="row">
						<div class="col-xs-12"><span>QE</span>732423234.34</div>
					</div>
				</div>
		</div>
		<!--End Dashboard Tab 5-->
	</div>
	<div class="clearfix"></div>
</div>
<!--End Dashboard 2 -->
<div style="height: 40px;"></div>
<script type="text/javascript">
//
// Draw Morris charts on Dashboard (panel- Statistics - Planning + 3 donuts)
//
function MorrisDashboard(){
	Morris.Line({
		element: 'stat-staff',
		data: [
			<?php
			$res = $_SESSION['stats'];
			foreach ($res as $value) {
			?>
			{"period": "<?php echo $value->{'month'} ?>", "Staff": <?php echo $value->{'number'} ?>},
			<?php
			}
			?>
		],
		xkey: 'period',
		ykeys: ['Staff'],
		labels: ['Staff']
	});

	Morris.Line({
		element: 'stat-user',
		data: [
			{"period": "2014-01", "Staff": 13.4},
			{"period": "2014-02", "Staff": 13.4},
			{"period": "2014-03", "Staff": 13.4},
			{"period": "2014-04", "Staff": 13.4},
			{"period": "2014-05", "Staff": 13.4},
			{"period": "2014-06", "Staff": 13.4},
		],
		xkey: 'period',
		ykeys: ['Staff'],
		labels: ['Staff']
	});
}
$(document).ready(function() {
	// Make all JS-activity for dashboard
	DashboardTabChecker();
	// Load Knob plugin and run callback for draw Knob charts for dashboard(tab-servers)
	LoadKnobScripts(DrawKnobDashboard);
	// Load Sparkline plugin and run callback for draw Sparkline charts for dashboard(top of dashboard + plot in tables)
	LoadSparkLineScript(DrawSparklineDashboard);
	// Load Morris plugin and run callback for draw Morris charts for dashboard
	LoadMorrisScripts(MorrisDashboard);
	// Make beauty hover in table
	$("#ticker-table").beautyHover();
	// Run script for stock block
	CreateStockPage();
});
</script>
