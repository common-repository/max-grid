<!DOCTYPE html>
<html>
<head>
<style type="text/css">
	.builder-version {
		font-family: "Helvetica Neue",sans-serif;
		position: absolute;
		bottom: 20px;
		left: 0;
		width: 100%;
		color: #4f5357;
		font-size: 10px;
		text-align: center;
	}
	#loading {
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		position: fixed;
		display: block;
		background-color: #23282d;;
		z-index: 99999999;
		text-align: center;
	}
	.loader,
	.loader:after {
	  border-radius: 50%;
	  width: 60px;
	  height: 60px;
	}
	.loader {
		position: absolute;
		top: 50%;
		left: 50%;
		font-size: 7px;
		text-indent: -9999em;
		border-top: 1.1em solid rgba(255,255,255,.2);
		border-right: 1.1em solid rgba(255,255,255,.2);
		border-bottom: 1.1em solid rgba(255,255,255,.2);
		border-left: 1.1em solid rgba(255,255,255,.1);
		-webkit-animation: load8 1.1s infinite linear;
		animation: load8 1.1s infinite linear;
		margin-top: -30px;
		margin-left: -30px;
	}
	@-webkit-keyframes load8 {
	  0% {
		-webkit-transform: rotate(0deg);
		transform: rotate(0deg);
	  }
	  100% {
		-webkit-transform: rotate(360deg);
		transform: rotate(360deg);
	  }
	}
	@keyframes load8 {
	  0% {
		-webkit-transform: rotate(0deg);
		transform: rotate(0deg);
	  }
	  100% {
		-webkit-transform: rotate(360deg);
		transform: rotate(360deg);
	  }
	}
</style>	
</head>
<body>
	<div id="loading">
	  	<div class="loader">Loading...</div>
		<span class="builder-version">Max Grid Builder - Preview Loader.</span>
	</div>
</body>
</html>