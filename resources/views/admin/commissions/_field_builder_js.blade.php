<script>
    $(document).ready(function() {
        $('#add-field').on('click', function(e) {
            e.preventDefault();
            addFieldRow();
        });
        $('.remove-field').on('click', function(e) {
            e.preventDefault();
            removeFieldRow($(this));
        })

        function addFieldRow() {
            var $clone = $('.field-row').clone();
            $('#fieldList').append($clone);
            $clone.removeClass('hide field-row');
            $clone.find('.remove-field').on('click', function(e) {
                e.preventDefault();
                removeFieldRow($(this));
            })
            attachFieldTypeListener($clone.find('.form-field-type'));
        }

        function removeFieldRow($trigger) {
            $trigger.parent().parent().remove();
        }

        $('#fieldList .field-list-entry').each(function(index) {
            attachFieldTypeListener($(this).find('.form-field-type'));
        });

        function attachFieldTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().parent().find('.chooseOptions');

                $cell.children().addClass('hide');
                $cell.children().children().val(null);

                if (val == 'choice' || val == 'multiple') {
                    $cell.children('.choiceOptions').addClass('show');
                    $cell.children('.choiceOptions').removeClass('hide');
                }
            });
        }
    });
</script>
