<?php

//dpm($jdata);
//dpm($_SESSION['tripal_synview_search']);

// prepare info for searching syntenic Blocks
$genome_id  = $_SESSION['tripal_synview_search']['SELECT_GENOME'];
$chr_id  = $_SESSION['tripal_synview_search']['SELECT_CHR'];
$chr     = $_SESSION['tripal_synview_search']['REF'][$genome_id][$chr_id];
//$start   = $_SESSION['tripal_synview_search']['START'];
//$end     = $_SESSION['tripal_synview_search']['END'];

$genome_info = chado_generate_var('analysis', array('analysis_id'=>$genome_id));

//$ac_left  = l("<<<", "synview/search/result/left");
//$ac_right = l(">>>", "synview/search/result/right");
$reference = "$chr";

print '<div class="row"> <div class="col-md-12">';

// print breadcrumb (go back for another search)
$nav = array();
$nav[] = l('Home', '/');
$nav[] = l('Search Syntenic Blocks', 'synview/search');
$nav[] = t('Search Result for Syntenic Blocks');
$breadcrumb = '<nav class="nav">' . implode(" > ", $nav) . '</nav><br>';
print $breadcrumb;

// print info for searching
print '<p><b>Selected genome and location: </b><br>';
print ' -> Genome: ' . $genome_info->name . '<br>';
print ' -> Location: ' . $reference . '<br></p>';
print '<p><b>Genome(s) for comparison: </b></p>';
print '</div></div>';

$tab_li = ''; $tab_content = ''; $tab_li_class = ''; $tab_content_class = ''; $tab_table = '';
$tab_n = 0;
$headers = array('Block' , 'Genome1 (location)', 'Genome2 (location)', 'score', 'evalue');

foreach ($jdata as $d) {
  if ($tab_n == 0) { $tab_li_class = 'class="active"'; } else { $tab_li_class = ''; }
  if ($tab_n == 0) { $tab_content_class = 'class="tab-pane active"'; } else { $tab_content_class = 'class="tab-pane"';}
  $display_name = $d['short_name'] ? $d['short_name'] : $d['name'];
  $tab_li .= '<li '.$tab_li_class.'> <a href="#panel-' . $d['canvas'] . '" data-toggle="tab">' . $display_name . '</a> </li>';

  // generate block table
  $rows = array();
  $n = 0;
  $color = '#DEEEEE';
  foreach ($d['linkCollection'] as $link) {
    $block_id = $link['bid'];
    $b = $blocks[$block_id];
    $block_id = l($block_id, "synview/block/". $block_id, array('attributes' => array('target' => "_blank")));
    $genome1 = $b->b1_genome . "<br>" . $b->b1_sid . " : ". $b->b1_fmin . " - ".$b->b1_fmax;
    $genome2 = $b->b2_genome . "<br>" . $b->b2_sid . " : ". $b->b2_fmin . " - ".$b->b2_fmax;

    if ($n % 2 == 1) {
        $rows[] = array(
          array('data'=> $block_id,  'width' => '10%', 'bgcolor' => $color),
          array('data'=> $genome1, 'width' => '20%', 'bgcolor' => $color),
          array('data'=> $genome2, 'width' => '20%', 'bgcolor' => $color),
          array('data'=> $b->score,  'width' => '10%', 'bgcolor' => $color),
          array('data'=> $b->evalue, 'width' => '10%', 'bgcolor' => $color),
        );
    } else {
        $rows[] = array(
          array('data'=> $block_id, 'width' => '10%'),
          array('data'=> $genome1, 'width' => '20%'),
          array('data'=> $genome2, 'width' => '20%'),
          array('data'=> $b->score, 'width' => '10%'),
          array('data'=> $b->evalue, 'width' => '10%'),
        );
    }
    $n++;
  }

  $table = array(
    'header' => $headers,
    'rows' => $rows,
    'attributes' => array(
      'id' => 'tripal_synview-search-result',
      'class' => 'tripal-data-table table'
    ),
    'sticky' => FALSE,
    'caption' => '',
    'colgroups' => array(),
    'empty' => '',
  );

  $tab_table = theme_table($table);
  $tab_content .= '<div '.$tab_content_class.' id="panel-' . $d['canvas'] . '"><div id="'.$d['canvas'].'"></div>'.$tab_table.'</div>';
  $tab_n++;
}

print '<div class="row">
		<div class="col-md-12">
			<div class="tabbable" id="tabs-syn">
				<ul class="nav nav-tabs">' . $tab_li . '</ul>
				<div class="tab-content">' . $tab_content . '</div>
			</div>
		</div>
	</div>';

// print button for moving left and right
//print '<button type="button" class="btn btn-default"> ' . $ac_left . '</button>';
//print "  $reference  ";
//print '<button type="button" class="btn btn-default"> ' . $ac_right. '</button>';

if (count($blocks) == 0) {
  ?><p>no block is found!</p><?php
} 

?>


