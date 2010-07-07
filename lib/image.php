<?php
require_once(dirname(__FILE__).'/../vendor/Image.php');
/*
 Saves resized image to media Directory
 and returns new path (relative to media directory)
 */
function resize_media($image_media, $width, $height) {
  if (empty($image_media)) return;
  list($image_path, $modified_path, $modified_media) = _media_filenames($image_media, '-resize-'.$width.'x'.$height);
  if (!file_exists($image_path)) return;  
  
  if (! file_exists($modified_path) ||
      filemtime($modified_path) < filemtime($image_path)) {
    $image = new GDImage($image_path);
    
    // preserve aspect ratio
    $old_width = (float) $image->width;
    $old_height = (float) $image->height;
    $old_ratio = $old_width / $old_height;
    
    $new_width = (float) $width;
    $new_height = (float) $height;
    $new_ratio = $width / $height;
    
    $resize_width = $width;
    $resize_height = $height;
    
    // figure out whether to keep width or height.
    if ($old_ratio < $new_ratio) {
      // keep height, redo width
      $resize_width = round($new_height * $old_ratio);
      
    } else if ($old_ratio > $new_ratio) {
      $resize_height = round($new_width * $old_height / $old_width);
    }
    // end preserve aspect ratio
    
    $image->resize($resize_width, $resize_height, false);
    $image->save($modified_path);
  }

  return $modified_media;  
}

function _media_filenames($image_media, $extra) {
  $media_directory = dirname($image_media);
  $filename_parts = explode('.', basename($image_media));
  $extension = array_pop($filename_parts);
  $basename = implode('.', $filename_parts);
  $modified_basename = $basename .$extra;
  $modified_media = $media_directory . '/'.$modified_basename .'.png';
  
  $image_path = config('media_path').$image_media;
  $modified_path = config('media_path').$modified_media;
  
  return array($image_path, $modified_path, $modified_media);
}


function crop_media($image_media, $width, $height) {
  if (empty($image_media)) return;
  list($image_path, $modified_path, $modified_media) = _media_filenames($image_media, '-crop-'.$width.'x'.$height);
  if (!file_exists($image_path)) return;  
  
  if (!file_exists($modified_path) ||
      filemtime($modified_path) < filemtime($image_path)) {
    $image = new GDImage($image_path);
    
    // preserve aspect ratio
    $old_width = (float) $image->width;
    $old_height = (float) $image->height;
    $old_ratio = $old_width / $old_height;
    
    $new_width = (float) $width;
    $new_height = (float) $height;
    $new_ratio = $width / $height;

    // figure out whether to keep width or height.
    if ($old_ratio < $new_ratio) {
      $image->resize($width, 0, false);
      $crop = ($image->height - $height)/2;
      $image->crop(0,$crop,$width,$crop+$height);
    } else if ($old_ratio > $new_ratio) {
      $image->resize(0, $height, false);
      $crop = ($image->width - $width)/2;
      $image->crop($crop,0,$crop+$width, $height);
    }
    // end preserve aspect ratio
    
    $image->save($modified_path);
  }
  return $modified_media;  
}