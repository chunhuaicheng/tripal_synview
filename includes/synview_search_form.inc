<?php

/**
 * @file
 *
 */

/**
 * generate form for search syn.
 */
function synview_search_form($form, $form_state) {
    
  // search block by genome, chr, start, end 
  $first_opt = array('SELECT_G' => 'Select a genome');

  $genome_opts = tripal_synview_get_genomes(TRUE);
  // hide disabled genomes
  $disabled_genomes = variable_get('tripal_synview_disabled_genomes', array());
  foreach ($disabled_genomes AS $id => $has_value) {
    if ($has_value != 0) {
      unset ($genome_opts[$id]);
    }
  }
  $form['#genome_opts']  = $genome_opts; // pass org array through from
  $genome_opts = $first_opt + $genome_opts;

  $form['LOCATION'] = array(
    '#type' => 'fieldset',
    '#title' => t('Search blocks by a given location.'),
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
    '#prefix' => '<div id="tripal_synview-location">',
    '#suffix' => '</div>',
  );
  
  // Handle the Genome Groups
  $genome_groups = variable_get('tripal_synview_genome_groups');
  $selected_group = isset($form_state['values']['GENOME_GROUPS']) ? $form_state['values']['GENOME_GROUPS'] : 0;
  $opt_groups = array('Select a Category');
  if ($genome_groups) {
    foreach(array_keys($genome_groups) AS $k) {
      $opt_groups[$k] = $k;
    }
    $gtitle = variable_get('tripal_synview_grouping_title', 'Genome Category');
    $form['LOCATION']['GENOME_GROUPS'] = array(
      '#type' => 'select',
      '#title' => $gtitle,
      '#description' => 'Select to show available genomes',
      '#options' => $opt_groups,
      '#default_value' => 0,
      '#ajax' => array(
        'event' => 'change',
        'callback' => 'tripal_synview_genome_group_ajax_callback',
        'method'   => 'replace',
        'wrapper'  => 'tripal_synview-location',
        'effect'   => 'fade',
      ),
    );
    if ($selected_group) {
      foreach ($genome_opts AS $gid => $gval) {
        if ($gid != 'SELECT_G' && !in_array($gid, array_keys($genome_groups[$selected_group]))) {
          unset($genome_opts[$gid]);
        }
      }
    }
  }

  if (!$genome_groups || $selected_group) {
    $form['LOCATION']['SELECT_GENOME'] = array(
      '#type' => 'select',
      '#title' => t('Genome:'),
      '#options' => $genome_opts,
      '#default_value' =>'SELECT_G', // put default db for different web
      '#ajax' => array(
        'event' => 'change',
        'callback' => 'ajax_synview_location_callback',
        'method'   => 'replace',
        'wrapper'  => 'id-textarea',
        'effect'   => 'fade',
      ),
    );
  
    // retrieve prev selection from session
    $second_opt = array('SELECT_C' => 'Select a chromosome/scaffold');
    if (isset($form_state['triggering_element']['#name']) && $form_state['triggering_element']['#name'] == 'SELECT_GENOME') {
      $gvalue = $form_state['triggering_element']['#value'];
      $second_opt += tripal_synview_get_ref_sequences($gvalue);
    }
    $form['LOCATION']['SELECT_CHR'] = array(
      '#type' => 'select',
      '#title' => t('Chromosome/Scaffold:'),
      '#prefix' => '<div id="id-textarea">',
      '#suffix' => '</div>',
      '#options' => $second_opt,
      '#default_value' => isset($_SESSION['tripal_synview_search']['SELECT_CHR']) ? $_SESSION['tripal_synview_search']['SELECT_CHR'] : 'SELECT_C',
    );
  
    //$form['LOCATION']['START'] = array(
    //  '#type' => 'textfield',
    //  '#title' => t('Start:'),
    //  '#maxlength' => 20,
    //  '#default_value' => isset($_SESSION['tripal_synview_search']['START']) ? $_SESSION['tripal_synview_search']['START'] : '',
    //  '#prefix' =>'<div id=xyz>',
    //);
    //$form['LOCATION']['END'] = array(
    //  '#type' => 'textfield',
    //  '#title' => t('End:'),
    //  '#maxlength' => 20,
    //  '#default_value' => isset($_SESSION['tripal_synview_search']['END']) ? $_SESSION['tripal_synview_search']['END'] : '',
    //  '#suffix' =>'</div>',
    //);
  
    $n = 0;
    foreach ($form['#genome_opts'] as $genome_id => $genome_name) {
      if ($genome_groups && $selected_group) {
        if ( !in_array($genome_id, array_keys($genome_groups[$selected_group]))) {
          continue;
        }
      }
      $label = "GENOME_" . $genome_id;
      $n++;
      if ($n == 1) {
        $form['LOCATION'][$label] = array(
          '#type' => 'checkbox',
          '#title' => $genome_name,
          '#default_value' => 0,
          '#prefix' => '<br><b>Choose (a) genome(s) for comparison:</b><br><span style="display: block; margin: 10px;">',
          '#suffix' => '</span>',
        );
      } else {
        $form['LOCATION'][$label] = array(
          '#type' => 'checkbox',
          '#title' => $genome_name,
          '#default_value' =>0,
          '#prefix' => '<span style="display: block; margin: 10px;">',
          '#suffix' => '</span>',
        );
      }
    }
  }

  $form['separator'] = array(
    '#markup' => '<h3>or</h3>',
    '#prefix' => '<div id="tripal_synview_form-separator" style="margin: 15px 0px">',
    '#suffix' => '</div>'
  );
  //.........................
  $form['ID'] = array(
    '#type' => 'fieldset',
    '#title' => t('Search a block by its ID.'),
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
  );

  $form['ID']['INPUT_ID'] = array(
    '#type' => 'textfield',
      '#title' => t('Example: ' . variable_get('tripal_synview_block_examples', t("'hjhnB1908' or 'abrjB0957'"))),
    '#maxlength' => 20,
  );

  // Submit
  //.........................
  $form['submit'] = array(
    '#type' => 'submit',
    '#default_value' => ' Search ',
  );

  $form['#prefix'] = '<div class="row"> <div class="col-md-9" style="margin-top:15px">';
  $form['#suffix'] = '</div> </div>';

  return $form;
}

/**
 * Validate the user options submitted via the above form.
 */
function synview_search_form_validate($form, &$form_state) {

  if (empty($form_state['values']['INPUT_ID'])) {
    // if no block ID is provided
    // the genome, chr, and ref genome should be required
    $genome_id = $form_state['values']['SELECT_GENOME'];
    $chr    = $form_state['values']['SELECT_CHR'];
    //$start  = $form_state['values']['START'];
    //$end    = $form_state['values']['END'];

    if (empty($genome_id)) {
      form_set_error('SELECT_GENOME', t('No genome selected. Choose a genome from the list.'));
    }

    if (!empty($genome_id) and $genome_id == 'SELECT_G') {
      form_set_error('SELECT_GENOME', t('No genome selected. Choose a genome from the list.'));
    }

    if (empty($chr)) {
      form_set_error('SELECT_CHR', t('No chromosome selected. Choose a chromosome from the list.'));
    }

    if (!empty($chr) and $chr == 'SELECT_C') {
      form_set_error('SELECT_CHR', t('No chromosome selected. Choose a chromosome from the list.'));
    }

    //if ($start and !preg_match('/^\d+$/', $start)) {
    //  form_set_error('START', 'Please enter a positive numeric value for the start');
    //}
    ///if ($end and !preg_match('/^\d+$/', $end)) {
    //  form_set_error('END', 'Please enter a positive numeric value for the end');
    //}
  
    //if ($start and $end) { 
    //  if ($end - $start + 1 > 10000000) {
    //    form_set_error('START', 'Please reduce the searching range to 10M bp');
    //    form_set_error('END', 'Please reduce the searching range to 10M bp');
    //  }
    //} 
    //elseif ($start) {
    //  $end = $start + 10000000 - 1;
    //  $form_state['values']['END'] = $end;
    //}
    //elseif ($end) {
    //  $start = $end - 10000000 + 1;
    //  if ($start < 1) {
    //    $start = 1;
    //  }
    //  $form_state['values']['START'] = $start;
    //} 
    //else {
    //  form_set_error('START', 'Please enter a positive numeric value for the start');
    //  form_set_error('END', 'Please enter a positive numeric value for the end');
    //}

    // check the end using reference length
/*     if ($genome_id and $chr and $end) {
      if (isset($_SESSION['tripal_synview_search']['RLEN'][$genome_id][$chr]) and 
          $end > $_SESSION['tripal_synview_search']['RLEN'][$genome_id][$chr]) {
        $end_pos = $_SESSION['tripal_synview_search']['RLEN'][$genome_id][$chr];
        $end_pos = "1 - $end_pos";
        form_set_error('END', 'The end position goes beyound the refernece ' . $end_pos);
      }
    } */

    $ref_genome = array();
    foreach ($form['#genome_opts'] as $genome_id => $genome_name) {
      $label = "GENOME_" . $genome_id;
      if ($form_state['values'][$label]) {
        $ref_genome[$genome_id] = $genome_name;
      }  
    }

    if (count($ref_genome) == 0) {
      form_set_error('LOCATION', t('No reference genome selected. Check one genome at least.'));
    }

    $form_state['refGenome'] = $ref_genome;
    $form_state['byFlag'] = 'location';
  } else {
    $form_state['byFlag'] = 'id';
  }

}

/**
 * Process the user options submitted.
 */
function synview_search_form_submit($form, &$form_state) {

  $error = FALSE;

  // search database
  if ($form_state['byFlag'] == 'location') {
    $genome_id = $form_state['values']['SELECT_GENOME'];
    $chr = $form_state['values']['SELECT_CHR'];
    //$start = $form_state['values']['START'];
    //$end = $form_state['values']['END'];
    $ref_genome = $form_state['refGenome']; 

    $_SESSION['tripal_synview_search']['SELECT_GENOME'] = $form_state['values']['SELECT_GENOME'];
    $_SESSION['tripal_synview_search']['SELECT_CHR']    = $form_state['values']['SELECT_CHR'];
    // $_SESSION['tripal_synview_search']['START']         = $form_state['values']['START'];
    // $_SESSION['tripal_synview_search']['END']           = $form_state['values']['END'];
    $_SESSION['tripal_synview_search']['refGenome']        = $form_state['refGenome'];

    drupal_goto("synview/search/result/1");
  } 
  elseif ($form_state['byFlag'] == 'id') {
    // if search block id, go to display_block page
    // otherwise, show error information
    $block_id = $form_state['values']['INPUT_ID'];
    $blk_info = db_query('SELECT value FROM {synblock} WHERE blockid=:block_id ', array(':block_id' => $block_id))->fetchField();
   
    if(!empty($blk_info)) {
      drupal_goto("synview/block/$block_id");
    } else {
      drupal_set_message("Can not find block ID: $block_id", 'warning');
    }
  }
  else {
    $error = TRUE;
    tripal_report_error(
      'synview',
      TRIPAL_ERROR,
      "Can not determine the search method byFlag."
    );
  }
}

/**
 * AJAX: change the selection of chr according genome.
 */
function ajax_synview_location_callback($form, $form_state) {
  return $form['LOCATION']['SELECT_CHR'];
}

function tripal_synview_genome_group_ajax_callback($form, $form_state) {
  return $form['LOCATION'];
}
