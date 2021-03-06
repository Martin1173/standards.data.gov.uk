<?php
// AT commerce

function at_commerce_field__profile_version(&$vars) {
  $output = '';

  // Use field description as a lable for all proposal assessment fields
  if ($vars['element']['#field_type'] == 'field_collection'){
    $entity_type = $vars['element']['#entity_type'];
    $field_name = $vars['element']['#field_name'];
    $bundle_name = $vars['element']['#bundle'];
    $instance_info = field_info_instance($entity_type, $field_name, $bundle_name);
    $label = $instance_info['description'];

    if ($field_name == 'field_ass_other') {
      foreach ($vars['items'] as $index => $item) {
        $assessment_other_question = array_pop($item['entity']['field_collection_item']);
        if (!isset($assessment_other_question['field_ass_question'])) {
          unset($vars['items'][$index]);
        }
      }
      if (empty($vars['items'])) {
        return ' '; // Hack to don't render 'Other:' label
      }
    }

    $vars['label'] = $label;
  }

  elseif ($vars['element']['#field_type'] == 'relation_endpoint') {
    $link_to_proposal = $vars['items'][0]['#rows'][0][0];
    $link_to_standard_version = $vars['items'][0]['#rows'][1][0];

    $relation = $vars['element']['#object'];

    if (isset($relation->endpoints[LANGUAGE_NONE][0]['entity_id'])) {
      $proposal = node_load($relation->endpoints[LANGUAGE_NONE][0]['entity_id']);
      if ($proposal->type == 'proposal') {
        $phase = $proposal->field_proposal_phase[LANGUAGE_NONE][0]['value'];
      }
    }

    // 0 = response, 1 = proposal, 2 = standard profile
    if ($phase == 1) {
     $draft = ' (draft)';
     $description = 'This is an assessment of a standard identified in a proposal. It is assessed for suitability against a set of criteria agreed by the <a href="/meeting/open-standards-board-terms-reference">Open Standards Board</a>. Note that a "No" response to a knock-out question means that this standard is not suitable for use in this context and will not be considered further.';
    }
    elseif ($phase == 2) {
     $draft = '';
     $description = 'This provides an assessment of a standard against a set of criteria that were agreed by the <a href="/meeting/open-standards-board-terms-reference">Open Standards Board</a>.';
    }

    $output = '<h1 class="article-title">Standard assessment' . $draft . '</h1>';

    $output .= '<p>' . $description . '</p>';

    $output .= '<h3>Standard profile:</h3>' . $link_to_proposal. '<h3>Standard:</h3>' . $link_to_standard_version . '<p></p>';

    return $output;
  }

  // Render the label, if it's not hidden.
  if (!$vars['label_hidden']) {
    $output .= '<h2 class="field-label"' . $vars['title_attributes'] . '>' . $vars['label'] . ':&nbsp;</h2>';
  }

  // Render the items.
  $output .= '<div class="field-items"' . $vars['content_attributes'] . '>';
  foreach ($vars['items'] as $delta => $item) {
    $classes = 'field-item ' . ($delta % 2 ? 'odd' : 'even');
    $output .= '<div class="' . $classes . '"' . $vars['item_attributes'][$delta] . '>' . drupal_render($item) . '</div>';
  }
  $output .= '</div>';

  // Render the top-level wrapper element.
  $tag = $vars['label_hidden'] ? 'div' : 'section';
  $output = "<$tag class=\"" . $vars['classes'] . '"' . $vars['attributes'] . '>' . $output . "</$tag>";

  return $output;

}

/**
 * Override or insert variables into the html template.
 */
function at_commerce_preprocess_html(&$vars) {
  global $theme_key;

  $vars['html_attributes'] .= 'id="html-no-iframe"';

  if(drupal_is_front_page()) {
    $vars['head_title'] = 'Welcome to the Standards Hub | Standards Hub';
  }

  $theme_name = 'at_commerce';
  $path_to_theme = drupal_get_path('theme', $theme_name);

  // Load the media queries styles
  $media_queries_css = array(
    $theme_name . '.responsive.style.css',
    $theme_name . '.responsive.gpanels.css'
  );
  load_subtheme_media_queries($media_queries_css, $theme_name);

  // Load IE specific stylesheets
  $ie_files = array(
    'IE 6'     => 'ie-6.css',
    'lte IE 7' => 'ie-lte-7.css',
    'IE 8'     => 'ie-8.css',
    'lte IE 9' => 'ie-lte-9.css',
  );
  load_subtheme_ie_styles($ie_files, $theme_name);

  // Add a class for the active color scheme
  if (module_exists('color')) {
    $class = check_plain(get_color_scheme_name($theme_key));
    $vars['classes_array'][] = 'color-scheme-' . drupal_html_class($class);
  }

  // Add class for the active theme
  $vars['classes_array'][] = drupal_html_class($theme_key);

  // Add browser and platform classes
  $vars['classes_array'][] = css_browser_selector();

  // Add theme settings classes
  $settings_array = array(
    'font_size',
    'body_background',
    'header_layout',
    'menu_bullets',
    'main_menu_alignment',
    'image_alignment',
    'site_name_case',
    'site_name_weight',
    'site_name_alignment',
    'site_name_shadow',
    'site_slogan_case',
    'site_slogan_weight',
    'site_slogan_alignment',
    'site_slogan_shadow',
    'page_title_case',
    'page_title_weight',
    'page_title_alignment',
    'page_title_shadow',
    'node_title_case',
    'node_title_weight',
    'node_title_alignment',
    'node_title_shadow',
    'comment_title_case',
    'comment_title_weight',
    'comment_title_alignment',
    'comment_title_shadow',
    'block_title_case',
    'block_title_weight',
    'block_title_alignment',
    'block_title_shadow',
    'corner_radius_form_input_text',
    'corner_radius_form_input_submit',
  );
  foreach ($settings_array as $setting) {
    $vars['classes_array'][] = theme_get_setting($setting);
  }

  // Font family settings
  $fonts = array(
    'bf'  => 'base_font',
    'snf' => 'site_name_font',
    'ssf' => 'site_slogan_font',
	  'mmf' => 'main_menu_font',
    'ptf' => 'page_title_font',
    'ntf' => 'node_title_font',
    'ctf' => 'comment_title_font',
    'btf' => 'block_title_font'
  );
  $families = get_font_families($fonts, $theme_key);
  if (!empty($families)) {
    foreach($families as $family) {
      $vars['classes_array'][] = $family;
    }
  }

  // Add Noggin module settings extra classes, not all designs can support header images
  if (module_exists('noggin')) {
    if (variable_get('noggin:use_header', FALSE)) {
      $va = theme_get_setting('noggin_image_vertical_alignment');
      $ha = theme_get_setting('noggin_image_horizontal_alignment');
      $vars['classes_array'][] = 'ni-a-' . $va . $ha;
      $vars['classes_array'][] = theme_get_setting('noggin_image_repeat');
      $vars['classes_array'][] = theme_get_setting('noggin_image_width');
    }
  }

  // Special case for PIE htc rounded corners, not all themes include this
  if (theme_get_setting('ie_corners') == 1) {
    drupal_add_css($path_to_theme . '/css/ie-htc.css', array(
      'group' => CSS_THEME,
      'browsers' => array(
        'IE' => 'lte IE 8',
        '!IE' => FALSE,
        ),
      'preprocess' => FALSE,
      )
    );
  }

  // Custom settings for AT Commerce
  // Content displays
  $show_frontpage_grid = theme_get_setting('content_display_grids_frontpage') == 1 ? TRUE : FALSE;
  $show_taxopage_grid = theme_get_setting('content_display_grids_taxonomy_pages') == 1 ? TRUE : FALSE;
  if ($show_frontpage_grid == TRUE || $show_taxopage_grid == TRUE) {drupal_add_js($path_to_theme . '/js/equalheights.js');}
  if ($show_frontpage_grid == TRUE) {
    $cols_fpg = theme_get_setting('content_display_grids_frontpage_colcount');
    $vars['classes_array'][] = $cols_fpg;
    drupal_add_js($path_to_theme . '/js/eq.fp.grid.js');
  }
  if ($show_taxopage_grid == TRUE) {
    $cols_tpg = theme_get_setting('content_display_grids_taxonomy_pages_colcount');
    $vars['classes_array'][] = $cols_tpg;
    drupal_add_js($path_to_theme . '/js/eq.tp.grid.js');
  }

  // Do stuff for the slideshow
  if (theme_get_setting('show_slideshow') == 1) {
    // Add some js and css
    drupal_add_css($path_to_theme . '/css/styles.slideshow.css', array(
      'preprocess' => TRUE,
      'group' => CSS_THEME,
      'media' => 'screen',
      'every_page' => TRUE,
      )
    );
    drupal_add_js($path_to_theme . '/js/jquery.flexslider-min.js');
    drupal_add_js($path_to_theme . '/js/slider.options.js');

    // Add some classes to do evil hiding of elements with CSS...
    if (theme_get_setting('show_slideshow_navigation_controls') == 0) {
      $vars['classes_array'][] = 'hide-ss-nav';
    }
    if (theme_get_setting('show_slideshow_direction_controls') == 0) {
      $vars['classes_array'][] = 'hide-ss-dir';
    }

    // Write some evil inline CSS in the head, oh er..
    $slideshow_width = check_plain(theme_get_setting('slideshow_width'));
    $slideshow_css = '.flexible-slideshow,.flexible-slideshow .article-inner,.flexible-slideshow .article-content,.flexslider {max-width: ' .  $slideshow_width . 'px;}';
    drupal_add_css($slideshow_css, array(
      'group' => CSS_DEFAULT,
      'type' => 'inline',
      )
    );
  }

  // Draw stuff
  drupal_add_js($path_to_theme . '/js/draw.js');

}

/**
 * Override or insert variables into the html template.
 */
function at_commerce_process_html(&$vars) {
  if (module_exists('color')) {
    _color_html_alter($vars);
  }
}

/**
 * Override or insert variables into the page template.
 */
function at_commerce_process_page(&$vars) {
  if (module_exists('color')) {
    _color_page_alter($vars);
  }

  // We some extra classes to support the fancy branding layouts
  $branding_classes = array();
  $branding_classes[] = $vars['linked_site_logo'] ? 'with-logo' : 'no-logo';
  $branding_classes[] = !$vars['hide_site_name'] ? 'with-site-name' : 'site-name-hidden';
  $branding_classes[] = $vars['site_slogan'] ? 'with-site-slogan' : 'no-slogan';
  $vars['branding_classes'] = implode(' ', $branding_classes);

  // Draw toggle text
  $toggle_text = t('Login/register');
  $vars['draw_link'] = '<a class="draw-toggle" href="#">' . check_plain($toggle_text) . '</a>';
}

/**
 * Preprocess variables for region.tpl.php
 *
 * Prepare the values passed to the theme_region function to be passed into a
 * pluggable template engine. Uses the region name to generate a template file
 * suggestions. If none are found, the default region.tpl.php is used.
 *
 * @see drupal_region_class()
 * @see region.tpl.php
 */
function at_commerce_preprocess_region(&$variables) {
  //only on 'Track progress' page
  if($variables['region'] == 'content' && current_path() == 'node/132'){
    $variables['classes_array'][] = 'two-50';
    $variables['classes_array'][] = 'gpanel';
    $variables['classes_array'][] = 'clearfix';
    $variables['classes_array'][] = 'no-padding';
    $variables['classes_array'][] = 'no-margin';
  }
}

/**
 * Override or insert variables into the node template.
 */
function at_commerce_preprocess_node(&$vars) {
  // Remove the horrid inline class, it does wanky things like display:inline on the UL, whack eh?
  $vars['content']['links']['#attributes']['class'] = 'links';

  // Clearfix node content wrapper
  $vars['content_attributes_array']['class'][] = 'clearfix';

  // Theming for node in block
  if (theme_get_setting('show_slideshow') == TRUE) {
    if (isset($vars['node']->nodesinblock)) {
      $vars['classes_array'][] = 'flexible-slideshow';
      $vars['title_attributes_array']['class'][] = 'element-invisible';
    }
  }

  // Content grids - nuke links off teasers if in a content_display
  if ($vars['view_mode'] == 'teaser') {
    $show_frontpage_grid = theme_get_setting('content_display_grids_frontpage') == 1 ? TRUE : FALSE;
    $show_taxopage_grid = theme_get_setting('content_display_grids_taxonomy_pages') == 1 ? TRUE : FALSE;
    if ($show_frontpage_grid == TRUE || $show_taxopage_grid == TRUE) {
      unset($vars['content']['links']);
    }
  }
}

/**
 * Override or insert variables into the comment template.
 */
function at_commerce_preprocess_comment(&$vars) {
  // Remove the horrid inline class, again, for gawds sake
  $vars['content']['links']['#attributes']['class'] = 'links';
}

function replace_spaces($match)
{
  return str_replace(" ","&nbsp;",$match[0]);
}


/**
 * Override or insert variables into the block template
 */
function at_commerce_preprocess_block(&$vars) {
  if ($vars['block']->module == 'superfish' || $vars['block']->module == 'nice_menu') {
    $vars['content_attributes_array']['class'][] = 'clearfix';
  }
  if (!$vars['block']->subject) {
    $vars['content_attributes_array']['class'][] = 'no-title';
  }
  if ($vars['block']->region == 'menu_bar' || $vars['block']->region == 'menu_bar_top') {
    $vars['title_attributes_array']['class'][] = 'element-invisible';

    $vars['content'] = preg_replace_callback('/>(.*?)</i', 'replace_spaces' , $vars['content']);
  }

  $track_progress_blocks = array(
      'block-views-workbench-moderation-block-1',
      'block-views-my-comments-block',
      'block-views-my-challenges-block',
      'block-views-my-challenges-block-1',
      'block-views-my-proposals-block',
      'block-views-my-proposals-block-1',
      'block-views-my-bookmarks-block-1',
      'block-views-comments-recent-block',
      'block-views-meeting-minutes-block',
      'block-views-surveys-block-1',
      );
  if(in_array($vars['block_html_id'], $track_progress_blocks)){
    $vars['classes_array'][] = 'region';
  }

  $track_progress_dividers = array(
      'block-block-5',
      'block-block-6',
      );
  if(in_array($vars['block_html_id'], $track_progress_dividers)){
    $vars['classes_array'][] = 'clear';
  }

}

/**
 * Override or insert variables into the field template.
 */
function at_commerce_preprocess_field(&$vars) {
  $element = $vars['element'];
  $vars['image_caption_teaser'] = FALSE;
  $vars['image_caption_full'] = FALSE;
  $vars['field_view_mode'] = '';
  $vars['classes_array'][] = 'view-mode-'. $element['#view_mode'];
  if(theme_get_setting('image_caption_teaser') == 1) {
    $vars['image_caption_teaser'] = TRUE;
  }
  if(theme_get_setting('image_caption_full') == 1) {
    $vars['image_caption_full'] = TRUE;
  }
  $vars['field_view_mode'] = $element['#view_mode'];
  // Vars and settings for the slideshow, we theme this directly in the field template
  $vars['show_slideshow_caption'] = FALSE;
  if (theme_get_setting('show_slideshow_caption') == TRUE) {
   $vars['show_slideshow_caption'] = TRUE;
  }
}

/**
 * Implements hook_css_alter().
 */
function at_commerce_css_alter(&$css) {
  // Replace all Commerce module CSS files with our own copies
  // for total control over all styles
  $path = drupal_get_path('theme', 'at_commerce');
  // cart
  $cart_css = drupal_get_path('module', 'commerce_cart') . '/theme/commerce_cart.css';
  if (isset($css[$cart_css])) {
    $css[$cart_css]['data'] = $path . '/css/commerce/commerce_cart.css';
  }
  // checkout
  $checkout_css = drupal_get_path('module', 'commerce_checkout') . '/theme/commerce_checkout.css';
  if (isset($css[$checkout_css])) {
    $css[$checkout_css]['data'] = $path . '/css/commerce/commerce_checkout.css';
  }
  $checkout_admin_css = drupal_get_path('module', 'commerce_checkout') . '/theme/commerce_checkout_admin.css';
  if (isset($css[$checkout_admin_css])) {
    $css[$checkout_admin_css]['data'] = $path . '/css/commerce/commerce_checkout_admin.css';
  }
  // customer
  $customer_css = drupal_get_path('module', 'commerce_customer') . '/theme/commerce_customer_ui.profile_types.css';
  if (isset($css[$customer_css])) {
    $css[$customer_css]['data'] = $path . '/css/commerce/commerce_customer_ui.profile_types.css';
  }
  // file (contrib)
  $file_css = drupal_get_path('module', 'commerce_file') . '/theme/commerce_file.forms.css';
  if (isset($css[$file_css])) {
    $css[$file_css]['data'] = $path . '/css/commerce/commerce_file.forms.css';
  }
  // line items
  $line_item_summary_css = drupal_get_path('module', 'line_item') . '/theme/commerce_line_item_summary.css';
  if (isset($css[$line_item_summary_css])) {
    $css[$line_item_summary_css]['data'] = $path . '/css/commerce/commerce_line_item_summary.css';
  }
  $line_item_ui_types_css = drupal_get_path('module', 'line_item') . '/theme/commerce_line_item_ui.types.css';
  if (isset($css[$line_item_ui_types_css])) {
    $css[$line_item_ui_types_css]['data'] = $path . '/css/commerce/commerce_line_item_ui.types.css';
  }
  $line_item_views_form_css = drupal_get_path('module', 'line_item') . '/theme/commerce_line_item_views_form.css';
  if (isset($css[$line_item_views_form_css])) {
    $css[$line_item_views_form_css]['data'] = $path . '/css/commerce/commerce_line_item_views_form.css';
  }
  // order
  $order_css = drupal_get_path('module', 'commerce_order') . '/theme/commerce_order.css';
  if (isset($css[$order_css])) {
    $css[$order_css]['data'] = $path . '/css/commerce/commerce_order.css';
  }
  $order_views_css = drupal_get_path('module', 'commerce_order') . '/theme/commerce_order_views.css';
  if (isset($css[$order_views_css])) {
    $css[$order_views_css]['data'] = $path . '/css/commerce/commerce_order_views.css';
  }
  // payment
  $payment_css = drupal_get_path('module', 'commerce_payment') . '/theme/commerce_payment.css';
  if (isset($css[$payment_css])) {
    $css[$payment_css]['data'] = $path . '/css/commerce/commerce_payment.css';
  }
  // price
  $price_css = drupal_get_path('module', 'commerce_price') . '/theme/commerce_price.css';
  if (isset($css[$price_css])) {
    $css[$price_css]['data'] = $path . '/css/commerce/commerce_price.css';
  }
  // product
  $product_css = drupal_get_path('module', 'commerce_product') . '/theme/commerce_product.css';
  if (isset($css[$product_css])) {
    $css[$product_css]['data'] = $path . '/css/commerce/commerce_product.css';
  }
  $product_ui_types_css = drupal_get_path('module', 'commerce_product') . '/theme/commerce_product_ui.types.css';
  if (isset($css[$product_ui_types_css])) {
    $css[$product_ui_types_css]['data'] = $path . '/css/commerce/commerce_product_ui.types.css';
  }
  $product_views_css = drupal_get_path('module', 'commerce_product') . '/theme/commerce_product_views.css';
  if (isset($css[$product_views_css])) {
    $css[$product_views_css]['data'] = $path . '/css/commerce/commerce_product_views.css';
  }
  // tax
  $tax_css = drupal_get_path('module', 'commerce_tax') . '/theme/commerce_tax.css';
  if (isset($css[$tax_css])) {
    $css[$tax_css]['data'] = $path . '/css/commerce/commerce_tax.css';
  }
}

/**
 * Returns HTML for a breadcrumb trail.
 */
function at_commerce_breadcrumb($vars) {
  $breadcrumb = $vars['breadcrumb'];
  $show_breadcrumb = theme_get_setting('breadcrumb_display');

  if (FALSE) {// || $show_breadcrumb == 'yes') {  Disable breadcrumbs

    $show_breadcrumb_home = theme_get_setting('breadcrumb_home');
    if (!$show_breadcrumb_home) {
      array_shift($breadcrumb);
    }
    if (!empty($breadcrumb)) {
      $heading = '<h2>' . t('You are here: ') . '</h2>';
      $separator = filter_xss(theme_get_setting('breadcrumb_separator'));
      $output = '';
      foreach ($breadcrumb as $key => $val) {
        if ($key == 0) {
          $output .= '<li class="crumb">' . $val . '</li>';
        }
        else {
          $output .= '<li class="crumb"><span>' . $separator . '</span>' . $val . '</li>';
        }
      }
      return $heading . '<ol id="crumbs">' . $output . '</ol>';
    }
  }
  return '';
}

/**
 * Returns HTML for a fieldset.
 */
function at_commerce_fieldset($vars) {
  $element = $vars['element'];
  element_set_attributes($element, array('id'));
  _form_set_class($element, array('form-wrapper'));

  $output = '<fieldset' . drupal_attributes($element['#attributes']) . '>';
  // add a class to the fieldset wrapper if a legend exists, in some instances they do not
  $class = "without-legend";
  if (!empty($element['#title'])) {
    // Always wrap fieldset legends in a SPAN for CSS positioning.
    $output .= '<legend><span class="fieldset-legend">' . $element['#title'] . '</span></legend>';
    // add a class to the fieldset wrapper if a legend exists, in some instances they do not
    $class = 'with-legend';
  }
  $output .= '<div class="fieldset-wrapper ' . $class  . '">';
  if (!empty($element['#description'])) {
    $output .= '<div class="fieldset-description">' . $element['#description'] . '</div>';
  }
  $output .= $element['#children'];
  if (isset($element['#value'])) {
    $output .= $element['#value'];
  }
  $output .= '</div>';
  $output .= "</fieldset>\n";
  return $output;
}