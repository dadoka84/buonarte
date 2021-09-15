<?php echo $this->doctype; ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $this->language; ?>">

<head>
<base href="<?php echo $this->base; ?>" />
<title><?php echo $this->title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->charset; ?>" />
<meta name="description" content="<?php echo $this->description; ?>" />
<meta name="keywords" content="<?php echo $this->keywords; ?>" />
<?php echo $this->robots; ?>
<script type="text/javascript" src="plugins/mootools/mootools.js"></script>
<script type="text/javascript" src="plugins/slimbox/js/slimbox.js"></script>
<script type="text/javascript" src="plugins/ufo/ufo.js"></script>
<?php echo $this->framework; ?>
<link rel="stylesheet" href="plugins/slimbox/css/slimbox.css" type="text/css" media="screen" />
<link rel="stylesheet" href="plugins/dpsyntax/dpsyntax.css" type="text/css" media="screen" />
<link rel="stylesheet" href="tl_files/music_academy/buon.css" type="text/css" media="screen" />
<?php echo $this->head; ?>


</head>

<body>
<div class="pozadina">
  <table width="95" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="897" height="145"><param name="movie" value="images/gore.swf" /><param name="quality" value="high" /><embed src="images/gore.swf" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="897" height="145"></embed></object></td>
    </tr>
    <tr>
      <td><table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
          <tr>
            <td width="252" align="center" valign="top"><table width="99%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="250" height="247"><param name="movie" value="images/left.swf" /><param name="quality" value="high" /><embed src="images/left.swf" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="250" height="247"></embed></object>
                      <br />
                      <br /></td>
                </tr>
                <tr>
                  <td class="lupa"><div align="left">
                      <div align="center">
                        <table width="90%" border="0" align="center" cellpadding="15" cellspacing="0">
                          <tr>
                            <td><span class="stubmapa"><span class="style5"><br>
                              Pretraga </span><br>
                              <?php echo $this->left; ?></span></td>
                          </tr>
                        </table>
                      </div>
                  </div></td>
                </tr>
                <tr>
                  <td class="pismo"><div align="center">
                      <table width="90%" border="0" align="center" cellpadding="15" cellspacing="0">
                        <tr>
                          <td><span class="style5"> Kontaktirajte nas!</span><br />
                              <img src="http://buonarte.com/images/blank.gif" width="1" height="5" /><br />
                              <span class="style3">Tel:  033/ 654-391<br />
                                Fax:  033/ 654-391</span><br /></td>
                        </tr>
                        <tr>
                          <td align="left" valign="top"><span class="stubmapa"><br>
                          <?php echo $this->right; ?></span></td>
                        </tr>
                      </table>
                  </div></td>
                </tr>
                <tr>
                  <td> </td>
                </tr>
            </table></td>
            <td width="14" class="kolona1"></td>
            <td width="617" align="left" valign="top"><?php echo $this->main; ?></td>
            <td width="14" class="kolona2"></td>
          </tr>
      </table></td>
    </tr>
    <tr>
      <td width="897" height="34" class="footersl"></td>
    </tr>
  </table>
</div>
<?php echo $this->mootools; ?>
</body>
</html>