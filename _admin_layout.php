<?php
function get_configured_title() {
  $menu_title = admin_find_title();
  if ($menu_title) {
    return $menu_title;
  } else {
    return view('title');
  }
}

function admin_find_title($menu='', $uri='') {
  if ($menu == '') {
    $menu = config('admin_menu');
    if (empty($menu)) {
      $menu = array();
    }
  }
  if ($uri == '') {
    $uri = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
  }
  
  foreach( $menu as $title => $link ) {
    if (is_array($link)) {
      $nested_title = admin_find_title( $link, $uri );
      if ($nested_title) {
        return $nested_title;
      }
    } else {
      if (! (false === strpos($uri, $link)) ) {
        return $title;
      }
    }
  }
  
  // try again with the referer param, if it exists
  $uri = parse_url($uri);
  $params = array();
  if (array_key_exists('query', $uri)) parse_str($uri['query'], $params);
  
  if (isset($params['referer']) && $params['referer'] != '') {
    return admin_find_title($menu, $params['referer']); 
  }
  
  return false;
}
view_set('title', get_configured_title());

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"  dir="ltr" lang="en-US">
  <head>
    <title><?php echo view('title') ?></title>
    <script type="text/javascript" src="<?php echo config('greenroom_path')->url ?>/vendor/jquery-1.4.2.min.js"></script>
    
    <link rel="stylesheet" href="<?php echo config('greenroom_path')->url ?>/vendor/blueprint/screen.css" type="text/css" media="screen, projection">
    <link rel="stylesheet" href="<?php echo config('greenroom_path')->url ?>/vendor/blueprint/print.css" type="text/css" media="print">
    <!--[if lt IE 8]>
      <link rel="stylesheet" href="<?php echo config('greenroom_path')->url ?>/vendor/blueprint/ie.css" type="text/css" media="screen, projection">
    <![endif]-->

    <?php Form::client_setup_html_head() ?>
    
  </head>
  <body>
    <div class="container">
      <h2>Blueline Admin</h2>
      <hr>
      <ul class="span-4 admin_menu"> 
      <?php
      // prepare menu by adding contextual information
      $menu_classes = array();
      foreach( config('admin_menu') as $label => $link ) {  
        if (view('title') == $label) $menu_classes[$label][] = 'current';
        if (is_array($link)) {
          if (in_array(view('title'), array_keys($link))) {
            $menu_classes[$label][] = 'current';
          }
        }
      }
      
      foreach( config('admin_menu') as $label => $link ) {
        // seperators
        if (is_string($link) && preg_match('/^-+$/', $link)) {
          ?><li><hr></li><?php
          continue;
        }
        
        if (isset($menu_classes[$label])) {
          $classes = implode(' ', $menu_classes[$label]);
        } else {
          $classes = '';
        }
        
        // individual links
        if (is_string($link)) {
          ?>
          <li class="<?php echo $classes ?>">
          <a href='<?php echo config('base_url') . $link ?>'><?php echo $label ?></a></li> 
          <?php
          continue;
        }
        // links with submenus
        if (is_array($link)) {
          $first_link = $link[array_shift(array_keys($link))];
          if (preg_match('|^http://|', $first_link)) {
            $first_link = '#';
          } else {
            $first_link = config('base_url').$first_link;
          }
          ?>
          <li class="<?php echo $classes ?>">
          <a href='<?php echo $first_link ?>' class="<?php echo $classes ?>"><?php echo $label ?></a> 
            <ul>
            <?php
            $sub_class = array('sub-nav');
            foreach( $link as $sub_label => $sub_link ) {
              if (view('title') == $sub_label) $sub_class[] = 'current';
              if (!preg_match('|^http://|', $sub_link)) {
                $sub_link = config('base_url').$sub_link;
              }
              ?><li class="<?php echo implode(' ', $sub_class) ?>"><a href='<?php echo $sub_link ?>'><?php echo $sub_label ?></a></li><?php
              $sub_class = array();
            }
            ?>
            </ul>
          </li> 
          <?php
          continue;
        }
      }
      ?>
      </ul>

      <div class="content span-20 last"><?php echo view('content'); ?></div>
      
      <hr>
      
      <div class="footer">
        &copy; <?php echo date('Y'); ?> <a href="http://responsivedevelopment.com">Responsive Development</a>
      </div>
      
    </div>
  </body>
</html>