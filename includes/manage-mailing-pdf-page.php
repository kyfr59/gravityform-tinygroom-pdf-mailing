<?php

// display content for custom menu item when selected
add_action( 'gform_export_page_publipostage_export_page', 'publipostage_export_page' );
function publipostage_export_page() {

    if ( ! GFCommon::current_user_can_any( 'gravityforms_export_entries' ) ) {
      wp_die( 'You do not have permission to access this page' );
    }

    // Include JS scripts for the page
    $scripts = array(
      'jquery-ui-datepicker',
      'gform_form_admin',
      'gform_field_filter',
      'sack',
    );
    foreach ( $scripts as $script ) {
      wp_enqueue_script( $script );
    }

    GFExport::page_header( __( 'Export Entries', 'gravityforms' ) );

        $choices = [];
        $choices['1_1'] = 'Ligne 1 - pos 1';
        $choices['1_2'] = 'Ligne 1 - pos 2';
        $choices['1_3'] = 'Ligne 1 - pos 3';
        $choices['2_1'] = 'Ligne 2 - pos 1';
        $choices['2_2'] = 'Ligne 2 - pos 2';
        $choices['2_3'] = 'Ligne 2 - pos 3';
        $choices['3_1'] = 'Ligne 3 - pos 1';
        $choices['3_2'] = 'Ligne 3 - pos 2';
        $choices['3_3'] = 'Ligne 3 - pos 3';
        $choices['4_1'] = 'Ligne 4 - pos 1';
        $choices['4_2'] = 'Ligne 4 - pos 2';
        $choices['4_3'] = 'Ligne 4 - pos 3';

        $options = "<option value=''></option>";
        foreach ($choices as $key => $choice) {
          $options .= '<option value="'.$key.'">'.$choice.'</option>';
        }

    ?>

    <script type="text/javascript">

      var gfSpinner;

      <?php GFCommon::gf_global(); ?>
      <?php GFCommon::gf_vars(); ?>

      function SelectExportForm(formId) {

        if (!formId)
          return;

        gfSpinner = new gfAjaxSpinner(jQuery('select#export_form'), gf_vars.baseUrl + '/images/spinner.gif', 'position: relative; top: 2px; left: 5px;');

        var mysack = new sack("<?php echo admin_url( 'admin-ajax.php' )?>");
        mysack.execute = 1;
        mysack.method = 'POST';
        mysack.setVar("action", "rg_select_export_form");
        mysack.setVar("rg_select_export_form", "<?php echo wp_create_nonce( 'rg_select_export_form' ); ?>");
        mysack.setVar("form_id", formId);
        mysack.onError = function () {
          alert(<?php echo json_encode( __( 'Ajax error while selecting a form', 'gravityforms' ) ); ?>)
        };
        mysack.runAJAX();

        return true;
      }

      function EndSelectExportForm(aryFields, filterSettings) {
        gfSpinner.destroy();

        if (aryFields.length == 0) {
          jQuery("#export_field_container, #export_date_container, #export_submit_container").hide()
          return;
        }

        var fieldList = "";
        for (var i = 0; i < aryFields.length; i++) {
          var meta_key = aryFields[i][0];
          console.log(meta_key);
          fieldList += "<li><select id='export_field_" + meta_key + "' name='export_field_" + meta_key + "' ><?php echo addslashes($options) ?></select>&nbsp;&nbsp;<label for='export_field_" + meta_key + "'>" + aryFields[i][1] + "</label></li>";
        }
        jQuery("#export_field_list").html(fieldList);
        jQuery("#export_date_start, #export_date_end").datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true});

        jQuery("#export_field_container, #export_filter_container, #export_date_container, #export_submit_container").hide().show();

        gf_vars.filterAndAny = <?php echo json_encode( esc_html__( 'Export entries if {0} of the following match:', 'gravityforms' ) ); ?>;
        jQuery("#export_filters").gfFilterUI(filterSettings);
      }

      ( function( $, window, undefined ) {

        $(document).ready(function() {
          $("#submit_button").click(function () {
            if ($(".gform_export_field:checked").length == 0) {
              //alert(<?php echo json_encode( __( 'Please select the fields to be exported', 'gravityforms' ) );  ?>);
              //return false;
            }
          });

          $('#export_form').on('change', function() {
            SelectExportForm($(this).val());
          }).trigger('change');
        });


      }( jQuery, window ));


    </script>

    <p class="textleft"><?php esc_html_e( 'Select a form below to export entries. Once you have selected a form you may select the fields you would like to export and then define optional filters for field values and the date range. When you click the download button below, Gravity Forms will create a CSV file for you to save to your computer.', 'gravityforms' ); ?></p>
    <div class="hr-divider"></div>
    <form id="gform_export" method="post" style="margin-top:10px;" target="_blank" action="<?php echo plugins_url() . '/gravityforms-tinygroom-pdf-mailing/mailing_pdf.php' ?>">
      <?php echo wp_nonce_field( 'rg_start_export', 'rg_start_export_nonce' ); ?>
      <table class="form-table">
        <tr valign="top">

          <th scope="row">
            <label for="export_form"><?php esc_html_e( 'Select A Form', 'gravityforms' ); ?></label> <?php gform_tooltip( 'export_select_form' ) ?>
          </th>
          <td>

            <select id="export_form" name="export_form">
              <option value=""><?php esc_html_e( 'Select a form', 'gravityforms' ); ?></option>
              <?php
              $forms = RGFormsModel::get_forms( null, 'title' );


              $forms = apply_filters( 'gform_export_entries_forms', $forms );

              foreach ( $forms as $form ) {
                ?>
                <option value="<?php echo absint( $form->id ) ?>" <?php selected( rgget( 'id' ), $form->id ); ?>><?php echo esc_html( $form->title ) ?></option>
                <?php
              }
              ?>
            </select>

          </td>
        </tr>
        <tr id="export_field_container" valign="top" style="display: none;">
          <th scope="row">
            <label for="export_fields"><?php esc_html_e( 'Select Fields', 'gravityforms' ); ?></label> <?php gform_tooltip( 'export_select_fields' ) ?>
          </th>
          <td>
            <ul id="export_field_list">
            </ul>
          </td>
        </tr>
        <tr id="export_filter_container" valign="top" style="display: none;">
          <th scope="row">
            <label><?php esc_html_e( 'Conditional Logic', 'gravityforms' ); ?></label> <?php gform_tooltip( 'export_conditional_logic' ) ?>
          </th>
          <td>
            <div id="export_filters">
              <!--placeholder-->
            </div>

          </td>
        </tr>
        <tr id="export_date_container" valign="top" style="display: none;">
          <th scope="row">
            <label for="export_date"><?php esc_html_e( 'Select Date Range', 'gravityforms' ); ?></label> <?php gform_tooltip( 'export_date_range' ) ?>
          </th>
          <td>
            <div>
                            <span style="width:150px; float:left; ">
                                <input type="text" id="export_date_start" name="export_date_start" style="width:90%" />
                                <strong><label for="export_date_start" style="display:block;"><?php esc_html_e( 'Start', 'gravityforms' ); ?></label></strong>
                            </span>

                            <span style="width:150px; float:left;">
                                <input type="text" id="export_date_end" name="export_date_end" style="width:90%" />
                                <strong><label for="export_date_end" style="display:block;"><?php esc_html_e( 'End', 'gravityforms' ); ?></label></strong>
                            </span>

              <div style="clear: both;"></div>
              <?php esc_html_e( 'Date Range is optional, if no date range is selected all entries will be exported.', 'gravityforms' ); ?>
            </div>
          </td>
        </tr>
      </table>
      <ul>
        <li id="export_submit_container" style="display:none; clear:both;">
          <br /><br />
          <button id="submit_button" class="button button-large button-primary"><?php esc_attr_e( 'Générer les étiquettes', 'gravityforms' ); ?></button>
                    <span id="please_wait_container" style="display:none; margin-left:15px;">
                        <i class='gficon-gravityforms-spinner-icon gficon-spin'></i> <?php esc_html_e( 'Exporting entries. Progress:', 'gravityforms' ); ?>
                      <span id="progress_container">0%</span>
                    </span>
        </li>
      </ul>
    </form>


    <?php
    GFExport::page_footer();

}