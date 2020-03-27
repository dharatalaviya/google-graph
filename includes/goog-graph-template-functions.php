<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Templates Functions
 * 
 * Handles to manage templates of plugin
 * 
 * @package Google Graph
 * @since 1.0.0
 */

/**
 * Returns the Google Graph templates directory
 * 
 * @package Google Graph
 * @since 1.0.0
 */
function goog_graph_user_meta_form(){
    global $current_user;

    $goog_graph_data = get_user_option( 'goog_graph_data', $current_user->ID, true);
      
     ?>
    <form name="goog_graph_user_meta" method="post">
        <?php for($i=0; $i<3; $i++){ ?>
        <label name "persent_label">Persentage <?php echo $i ?>:</label>
        <input type="number" min="1" max="100" name="goog_graph_data[<?php echo $i ?>][persentage]" id="goog_graph_data[<?php echo $i ?>]['persentage']" value="<?php echo  $goog_graph_data[$i]['persentage'] ?>">
        <label name="year_label">Year <?php echo $i ?>:</label>
        <input type="number" min="1900" max="2019" name="goog_graph_data[<?php echo $i ?>][year]" id="goog_graph_data[<?php echo $i ?>]['year']" value="<?php echo  $goog_graph_data[$i]['year'] ?>">
        <br/>
        <?php } ?>
        <input type="submit" name="goog_graph_sumit" id="goog_graph_sumit" value="Submit"> 
        
    </form>
    <?php
    
    if(!empty( $_POST['goog_graph_data'])){
        $goog_graph_data_option = $_POST['goog_graph_data'];
      
        update_user_option($current_user -> ID, 'goog_graph_data', $goog_graph_data_option,false);
    }

}

/**
 * Display Google Graph with user value
 * 
 * @package Google Graph
 * @since 1.0.0
 */
function goog_graph_display()
{
    global $current_user;

    $goog_graph_data = get_user_option( 'goog_graph_data', $current_user->ID, true );
    //print_r($goog_graph_data);
    
    if(!empty($goog_graph_data)){
     
        echo '<div id="curve_chart" style="width: 900px; height: 500px"></div>';
    }
    
}