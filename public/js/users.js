let IDUsersSelected = [];

$(document).ready(function() {
});

// When checkboxes with name "checkBoxUser" are checked put the id in the array
$(document).on('change', 'input[name="checkBoxUser"]', function() {
    if ($(this).is(':checked')) {
        // push the "id" of the checkbox to the array
        IDUsersSelected.push($(this)[0].id);
    } else {
        IDUsersSelected.splice(IDUsersSelected.indexOf($(this)[0].id), 1);
    }
    gererButtons();

});


function gererButtons()
{
    if (IDUsersSelected.length === 0) {

        if ($('#deactivateUsers').hasClass('btn-secondary')) {
            // RESTE INACTIF
        }
        else if ($('#deactivateUsers').hasClass('btn-warning'))
        {
            handleStyleButton('deactivateUsers');
        }
        if ($('#deleteUsers').hasClass('btn-secondary')) {
            // RESTE INACTIF
        }
        else if ($('#deleteUsers').hasClass('btn-danger'))
        {
            handleStyleButton('deleteUsers');
        }

        $(document).off('click', '#deactivateUsers');
        $(document).off('click', '#deleteUsers');
    }
    else if (IDUsersSelected.length > 0)
    {
        // foreach element in IDUsersSelected if the field "Actif ?" is "Non" then disable the "deactivateUsers" button
        let nonActif = false;
        $.each(IDUsersSelected, function(index, value) {
            if ($('#' + value).closest('tr').find('td:eq(7)').text().includes('Non')) {
                nonActif = true;
            }
        });
        if (nonActif) {
            if ($('#deactivateUsers').hasClass('btn-secondary')) {
                // RESTE INACTIF
            }
            else if ($('#deactivateUsers').hasClass('btn-warning'))
            {
                handleStyleButton('deactivateUsers');
            }
            $(document).off('click', '#deactivateUsers');

        }
        else
        {
            if ($('#deactivateUsers').hasClass('btn-warning')) {
                // RESTE ACTIF
            }
            else if ($('#deactivateUsers').hasClass('btn-secondary'))
            {
                handleStyleButton('deactivateUsers');
            }
            $(document).on('click', '#deactivateUsers', function() {
                desactivationUser();
            });
        }
        if ($('#deleteUsers').hasClass('btn-danger')) {
            // RESTE ACTIF
        }
        else if ($('#deleteUsers').hasClass('btn-secondary'))
        {
            handleStyleButton('deleteUsers');
        }

        $(document).on('click', '#deleteUsers', function() {
            deleteUsers();
        });
    }

}

function handleStyleButton(typeBTN){
    // if button has class btn-secondary then remove it and add the class btn-warning
    if (typeBTN === 'deactivateUsers') {
        if ($('#deactivateUsers').hasClass('btn-secondary')) {
            $('#deactivateUsers').removeClass('btn-secondary');
            $('#deactivateUsers').addClass('btn-warning');
            $('#deactivateUsers').attr("style", "cursor:default");
        }
        else if ($('#deactivateUsers').hasClass('btn-warning')) {
            $('#deactivateUsers').removeClass('btn-warning');
            $('#deactivateUsers').addClass('btn-secondary');
            $('#deactivateUsers').attr("style", "cursor:not-allowed");
        }
    }
    else if (typeBTN === 'deleteUsers') {
        if ($('#deleteUsers').hasClass('btn-secondary')) {
            $('#deleteUsers').removeClass('btn-secondary');
            $('#deleteUsers').addClass('btn-danger');
            $('#deleteUsers').attr("style", "cursor:default");
        }
        else if ($('#deleteUsers').hasClass('btn-danger')) {
            $('#deleteUsers').removeClass('btn-danger');
            $('#deleteUsers').addClass('btn-secondary');
            $('#deleteUsers').attr("style", "cursor:not-allowed");
        }
    }
}

function resetButtons(typeBTN)
{
        $('#deactivateUsers').removeClass('btn-warning');
        $('#deactivateUsers').addClass('btn-secondary');
        $('#deactivateUsers').attr("style", "cursor:not-allowed");

        $('#deleteUsers').removeClass('btn-danger');
        $('#deleteUsers').addClass('btn-secondary');
        $('#deleteUsers').attr("style", "cursor:not-allowed");

}

function deleteUsers()
{
    if (IDUsersSelected.length > 0) {
        $.each(IDUsersSelected, function(index, value) {
            $.ajax({
                url: '/user/deleteMessage/' + value,
                type: 'GET',
                async: true,
                success: function(result) {
                    // Remove the row from the table
                    $('#' + value).closest('tr').remove();
                    // empty the array
                    IDUsersSelected = [];
                    // unselect all checkboxes
                    $('input[name="checkBoxUser"]').prop('checked', false);
                    resetButtons();
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('L\'utilisateur ' + $('#' + value).closest('tr').find('td:eq(4)').text() + ' ne peut pas être supprimé');
                }
            });
        });
        //alert(IDUsersSelected.length + ' utilisateur(s) supprimé(s)');
    }

}

function desactivationUser()
{
    // if any of the field "Actif ?" from the selected users is "Non" then alert smth
    let nonActif = false;
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
                    IDUsersSelected = [];
                    $('input[name="checkBoxUser"]').prop('checked', false);
                    resetButtons();
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('Error!  Status = ' + xhr.status);
                }
            });
        });

        alert(IDUsersSelected.length + ' utilisateur(s) désactivé(s)');
    }
}

$(document).on('click', '#deactivateUsers', function() {
    // NOTHING
});

// When the button with id "deleteUsers" is clicked, make an ajax call foreach id in the array to delete the user
$(document).on('click', '#deleteUsers', function() {
    // NOTHING
});

