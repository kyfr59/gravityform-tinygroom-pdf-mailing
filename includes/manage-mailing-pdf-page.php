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

        // Retrieve forms preselections from database
        global $wpdb;
        $table_name = $wpdb->prefix . "gf_tinygroom_pdf_mailing";
        $forms_config_results = $wpdb->get_results( "SELECT * FROM $table_name" );

        foreach($forms_config_results as $form_config) {
          $post_values = unserialize($form_config->post_values);
          foreach($post_values as $key => $value) {
            if (strpos($key, 'export_field_') === 0 && !empty($value)) {
              $k = str_replace('_', '.', $key);
              $k = str_replace('export.field.', 'export_field_', $k);
              $selected[$form_config->form_id][$k] = $value;
            }
          }
        }

    ?>
    <script type="text/javascript">

      var gfSpinner;
      var currentFormId;
      var selected = <?php echo @json_encode($selected) ?>;

      <?php GFCommon::gf_global(); ?>
      <?php GFCommon::gf_vars(); ?>

      function SelectExportForm(formId) {

        if (!formId)
          return;

        currentFormId = formId;

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
          jQuery("#export_field_container, #export_date_container, #export_submit_container, #export_first_tag_position, #export_page_format").hide()
          return;
        }

        var fieldList = "";
        for (var i = 0; i < aryFields.length; i++) {
          var meta_key = aryFields[i][0];

          fieldList += "<li><select id='export_field_" + meta_key + "' name='export_field_" + meta_key + "' >';";
          fieldList += "<option value=''></option>";
          fieldList += "<option value='1_1' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '1_1') fieldList += " selected='selected'" ;
          fieldList += " >Ligne 1 - pos 1</option>";
          fieldList += "<option value='1_2' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '1_2') fieldList += " selected='selected'" ;
          fieldList += ">Ligne 1 - pos 2</option>";
          fieldList += "<option value='1_3' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '1_3') fieldList += " selected='selected'" ;
          fieldList += ">Ligne 1 - pos 3</option>";
          fieldList += "<option value='2_1' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '2_1') fieldList += " selected='selected'" ;
          fieldList += ">Ligne 2 - pos 1</option>";
          fieldList += "<option value='2_2' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '2_2') fieldList += " selected='selected'" ;
          fieldList += ">Ligne 2 - pos 2</option>";
          fieldList += "<option value='2_3' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '2_3') fieldList += " selected='selected'" ;
          fieldList += ">Ligne 2 - pos 3</option>";
          fieldList += "<option value='3_1' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '3_1') fieldList += " selected='selected'" ;
          fieldList += ">Ligne 3 - pos 1</option>";
          fieldList += "<option value='3_2' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '3_2') fieldList += " selected='selected'" ;
          fieldList += ">Ligne 3 - pos 2</option>";
          fieldList += "<option value='3_3' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '3_3') fieldList += " selected='selected'" ;
          fieldList += ">Ligne 3 - pos 3</option>";
          fieldList += "<option value='4_1' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '4_1') fieldList += " selected='selected'" ;
          fieldList += ">Ligne 4 - pos 1</option>";
          fieldList += "<option value='4_2' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '4_2') fieldList += " selected='selected'" ;
          fieldList += ">Ligne 4 - pos 2</option>";
          fieldList += "<option value='4_3' ";
          if(selected && selected[currentFormId]['export_field_' + meta_key] == '4_3') fieldList += " selected='selected'" ;
          fieldList += ">Ligne 4 - pos 3</option>";
          fieldList += "</select>&nbsp;&nbsp;<label for='export_field_" + meta_key + "'>" + aryFields[i][1] + "</label></li>";
        }
        jQuery("#export_field_list").html(fieldList);
        jQuery("#export_date_start, #export_date_end").datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true});

        jQuery("#export_field_container, #export_filter_container, #export_date_container, #export_submit_container, #export_first_tag_position, #export_page_format").hide().show();

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

        <tr id="export_first_tag_position" valign="top" style="display: none;">
          <th scope="row">
            <label for="export_date">Position de la première étiquette sur la planche</label>
          </th>
          <td><input style="width:35px;" type="text" name="first_tag_position" value="1" /></td>
        </tr>

        <tr id="export_page_format" valign="top" style="display: none;">
          <th scope="row">
            <label for="export_date">Position de la première étiquette sur la planche</label>
          </th>
          <td>
            <table>
              <tr><td style="padding:0;">Marge de gauche :&nbsp;</td><td style="padding:0;"><input name="marginLeft" type="text" style="width:35px;" value="3"> centimètres</td></tr>
              <tr><td style="padding:0;">Marge du haut :&nbsp;</td><td style="padding:0;"><input name="marginTop" type="text" style="width:35px;" value="2"> centimètres</td></tr>
              <tr><td style="padding:0;">Marge à droite des étiquettes :&nbsp;</td><td style="padding:0;"><input name="SpaceX" type="text" style="width:35px;" value="0.5"> centimètres</td></tr>
              <tr><td style="padding:0;">Marge à gauche des étiquettes  :&nbsp;</td><td style="padding:0;"><input name="SpaceY" type="text" style="width:35px;" value="1"> centimètres</td></tr>
              <tr><td style="padding:0;">Largeur des étiquettes  :&nbsp; </td><td style="padding:0;"><input name="width" type="text" style="width:35px;" value="68"> centimètres</td></tr>
              <tr><td style="padding:0;">Hauteur des étiquettes  :&nbsp; </td><td style="padding:0;"><input name="height" type="text" style="width:35px;"  value="36"> centimètres</td></tr>
              <tr><td style="padding:0;">Taille de la police  :&nbsp;</td><td style="padding:0;"><input name="fontSize" type="text" style="width:35px;" value="10"> pixels</td></tr>
            </table>
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