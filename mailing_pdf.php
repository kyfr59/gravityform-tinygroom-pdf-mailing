<?php

// Include Wordpress logic & PDFLabel library
require_once(__DIR__ . "/../../../wp-load.php");

if(!current_user_can('administrator') && !current_user_can('author')) {
    header('Location: https://tiny-groom.com/');
    exit();
}
require_once (__DIR__ . '/includes/fpdf/fpdf_label.php');

// Save form configuration in database
$wpdb->delete( $wpdb->prefix . "gf_tinygroom_pdf_mailing", array( 'form_id' => $_POST['export_form'] ) );
$wpdb->insert(
    $wpdb->prefix . "gf_tinygroom_pdf_mailing",
    array(
        'form_id' => $_POST['export_form'],
        'post_values' => serialize($_POST)
    )
);

// Retrieve form criterias
if ( ! empty( $_POST['export_date_start'] ) ) {
    $search_criteria['start_date'] = $_POST['export_date_start'];
}

if ( ! empty( $_POST['export_date_end'] ) ) {
    $search_criteria['end_date'] = $_POST['export_date_end'];
}

$search_criteria['status'] = 'active';

$form_id = $_POST['export_form'];
$form    = RGFormsModel::get_form_meta( $form_id );
$search_criteria['field_filters'] = GFCommon::get_field_filters_from_post( $form );


// Handle tag position on page
if (empty( $_POST['first_tag_position'])) {
    $first_tag_position = 1;
} else {
    $first_tag_position = $_POST['first_tag_position'];
}

// Handle sorting
$sorting = array( 'key' => 'id', 'direction' => 'DESC', 'type' => 'info' );


// Get entries
$entries = GFAPI::get_entries( $form_id, $search_criteria, $sorting);

// Handle choosen position from form
$positions = [];
foreach($_POST as $key => $position) {
    if (strpos($key, 'export_field_') === 0 && !empty($position)) {
        $field = str_replace('export_field_', '', $key);
        $field = str_replace('_', '.', $field);

        $line  = explode('_', $position)[0];
        $order = explode('_', $position)[1];

        $positions[$line][$order] = $field;
    }
}

ksort($positions);
foreach($positions as $key => $orders) {
    ksort($orders);
    $positions[$key] = $orders;
}


// Mailing format params
$marginLeft = 3;
$marginTop  = 2;
$SpaceX     = 0.5;
$SpaceY     = 1;
$width      = 68;
$height     = 36;
$fontSize   = 10;

// Prepare mailing
$pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>$marginLeft, 'marginTop'=>$marginTop, 'NX'=>3, 'NY'=>8, 'SpaceX'=>$SpaceX, 'SpaceY'=>$SpaceY, 'width'=>$width, 'height'=>$height, 'font-size'=>$fontSize));
$pdf->AddPage();

// Add potentials blanks tags
for($i = 1; $i < $first_tag_position ; $i++) {
    $pdf->Add_Label("");
}

// Generate labels
foreach($entries as $entry) {

    $text = '';
    $line1 = $line2 = $line3 = $line4 = '';

    $etiquette = [];

    // First line of mailing
    if (isset($positions[1])) {
        foreach($positions[1] as $order) {
            if(!empty($entry[$order])) {
                $line1 .= utf8_decode($entry[$order]).' ';
            }
        }
    }

    // Second line of mailing
    if (isset($positions[2])) {
        foreach($positions[2] as $order) {
            if(!empty($entry[$order])) {
                $line2 .= utf8_decode($entry[$order]).' ';
            }
        }
    }

    // Third line of mailing
    if (isset($positions[3])) {
        foreach($positions[3] as $order) {
            if(!empty($entry[$order])) {
                $line3 .= utf8_decode($entry[$order]).' ';
            }
        }
    }

    // Fourth line of mailing
    if (isset($positions[4])) {
        foreach($positions[4] as $order) {
            if(!empty($entry[$order])) {
                $line3 .= utf8_decode($entry[$order]).' ';
            }
        }
    }

    if (strlen(trim($line1)))     $text .= trim($line1) ."\n";
    if (strlen(trim($line2)))     $text .= trim($line2) ."\n";
    if (strlen(trim($line3)))     $text .= trim($line3) ."\n";
    if (strlen(trim($line4)))     $text .= trim($line4) ."\n";

    $pdf->Add_Label($text);
}

$pdf->Output();