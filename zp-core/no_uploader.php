<?php
printAdminHeader('upload','albums');
echo "\n</head>";
echo "\n<body>";
printLogoAndLinks();
?>
<div id="main">
	<?php
	printTabs();
	?>
	<div id="content">
		<p class="notebox">
		<?php echo gettext('There are no upload handlers enabled that can service your request.')?>
		</p>
	</div><!-- content -->
</div><!-- main -->
<?php
printAdminFooter();
?>
</body>
</html>
