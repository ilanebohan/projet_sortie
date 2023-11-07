var IDUsersSelected = [];

// When checkboxes with name "checkBoxUser" are checked put the id in the array
$(document).on('change', 'input[name="checkBoxUser"]', function() {
    if ($(this).is(':checked')) {
        // push the "id" of the checkbox to the array
        IDUsersSelected.push($(this)[0].id);

        // if the field "Actif ?" is "Non" then disable the "deactivateUsers" button
        if ($('#' + $(this)[0].id).closest('tr').find('td:eq(7)').text() == 'Non') {
            $('#deactivateUsers').prop('disabled', true);
        }
    } else {
        IDUsersSelected.splice(IDUsersSelected.indexOf($(this)[0].id), 1);
    }
});

// When the button with id "deleteUsers" is clicked, make an ajax call foreach id in the array to delete the user
$(document).on('click', '#deleteUsers', function() {
    if (IDUsersSelected.length > 0) {
        $.each(IDUsersSelected, function(index, value) {
            $.ajax({
                url: '/user/delete/' + value,
                type: 'GET',
                async: true,
                success: function(result) {
                    // Remove the row from the table
                    $('#' + value).closest('tr').remove();
                    //console.log(result);

                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('Error!  Status = ' + xhr.status);
                }
            });
        });
        alert(IDUsersSelected.length + ' utilisateur(s) supprimé(s)');
    }
});


$(document).on('click', '#deactivateUsers', function() {
    // if any of the field "Actif ?" from the selected users is "Non" then alert smth
    var nonActif = false;
    $.each(IDUsersSelected, function(index, value) {
        if ($('#' + value).closest('tr').find('td:eq(7)').text().includes('Non')) {
            nonActif = true;
        }
    });
    if (nonActif) {
        alert('Un ou plusieurs utilisateur(s) sélectionné(s) sont déjà désactivés !');
        // stop the function execution
        return;
    }
    console.log(nonActif);
    if (IDUsersSelected.length > 0) {
        $.each(IDUsersSelected, function(index, value) {
            $.ajax({
                url: '/user/desactivate/' + value,
                type: 'GET',
                success: function(result) {
                    // Update the 'Actif ?' row of the user
                    $('#' + value).closest('tr').find('td:eq(7)').text('Non');

                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('Error!  Status = ' + xhr.status);
                }
            });
        });

        alert(IDUsersSelected.length + ' utilisateur(s) désactivé(s)');
    }
});

