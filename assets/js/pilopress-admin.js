(function ($) {

  $(document).ready(function () {

    var $title = $('#title');
    var $prepend = $('.acf-input-prepend span');
    var $layoutSlug = $('#acf_field_group-_pip_layout_slug');
    var $layoutTemplate = $('#acf_field_group-_pip_render_layout');
    var $renderCSS = $('#acf_field_group-_pip_render_style');
    var $renderSCSS = $('#acf_field_group-_pip_render_style_scss');
    var $renderScript = $('#acf_field_group-_pip_render_script');
    var layoutSwitch = false;

    /**
     * When something is typed in "title" field
     */
    $title.on('input', function () {
      // Get title
      var $this = $(this);

      // If new layout
      if ($('#auto_draft').val() === '1' && !layoutSwitch) {
        // Change values with sanitized slug
        change_values($this, true);
      }
    });

    /**
     * When something is typed in "layout slug" field
     */
    $layoutSlug.on('input', function () {
      // Get layout slug
      var $this = $(this);

      layoutSwitch = true;

      // Change values with sanitized slug
      change_values($this);

      if (!$this.val()) {
        $prepend.html('layout');
        $this.focus();
      }
    });

    /**
     * Change input & span values
     * @param $this
     * @param draft
     */
    function change_values ($this, draft = false) {
      $layoutSlug.val(sanitize_title($this.val()));
      $prepend.html(sanitize_title($this.val()));

      if (draft) {
        updateRenderSettings($this.val());
      }
    }

    /**
     * Change render settings values
     * @param val
     */
    function updateRenderSettings (val) {
      $layoutTemplate.val(sanitize_title(val) + '.php');
      $renderCSS.val(sanitize_title(val) + '.css');
      $renderSCSS.val(sanitize_title(val) + '.scss');
      $renderScript.val(sanitize_title(val) + '.js');
    }

    /**
     * Sanitize value like wp function "sanitize_title"
     * @param $val
     * @returns {string}
     */
    function sanitize_title ($val) {
      return $val.toLowerCase()
        .replace(/\s+/g, '-')       // Replace spaces with -
        .replace(/[^\w\-]+/g, '')   // Remove all non-word chars
        .replace(/\-\-+/g, '-')     // Replace multiple - with single -
        .replace(/\_\_+/g, '_')     // Replace multiple _ with single _
        .replace(/^-+/, '');         // Trim - from start of text
    }
  });

})(jQuery);
