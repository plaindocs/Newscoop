<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Packaged templates #03</title>
<link rel="stylesheet" type="text/css" href="/templates/style03.css">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="alternate" type="application/rss+xml" title="Packaged templates #3" href="http://{{ $campsite->publication->site }}{{ uripath options="template rss.tpl" }}?{{ urlparameters options="template rss.tpl" }}" >

</head>

<body marginheight="5" marginwidth="5" bgcolor="#FFFFFF">
<SCRIPT language="JavaScript">
<!--
if ((screen.width<=300) && (screen.height<=300))
{
 window.location="http://www.mediaonweb.org{{ uri options="template test.tpl" }}";
}
//-->
</SCRIPT>
<table width="760" cellpadding="0" cellspacing="0" border="0">

<!-- iznad headera -->

  <tr>
    <td>{{ include file="header.tpl" }}            </td>
  </tr>

<!-- end iznad headera -->

<!-- header -->

<!-- end header -->
  
<!-- horizontal bar -->

<!-- end horizonat bar -->

<!-- front kolone -->

  <tr>
    <td>
	  <table width="100%" cellpadding="0" cellspacing="0" border="0">
	    <tr>
	      <td width="118" valign="top">
		  
		    <!-- leva kolona -->
			{{ include file="left.tpl" }}
                    <!-- linkovi -->
			
			{{ include file="links.tpl" }}
                    <!-- end linkovi -->
            <!-- banner radio tocak -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
			  <tr>
			    <td height="5" bgcolor="#FFFFFF"></td>
			  </tr>
			  <tr>
			    <td align="center">{{ include file="left-banners.tpl" }}</td>
			  </tr>
			</table>
			
			<!-- end banner radio tocak -->
			
			<!-- end leva kolona -->
			
		  </td>
		  <td width="13" bgcolor="#FFFFFF"></td>
	      <td width="491" valign="top">
		  
		    <!-- srednja kolona -->
			
			
			
			<!-- end srednja kolona -->
			{{ include file="home-middle.tpl" }}<br>
		  </td>
		  <td width="13" bgcolor="#FFFFFF"></td>
	      <td width="125" valign="top">
		  
		    <!-- desna kolona -->
			
			{{ include file="right.tpl" }}
			
			<!-- end desna kolona -->
			
		  </td>
		</tr>
	  </table>
	</td>
  </tr>

<!-- end front kolone -->

<!-- footer -->

  <tr>
    <td>
	  {{ include file="footer.tpl" }}
	</td>
  </tr>

<!-- end footer -->

</table>

</body>
</html>
