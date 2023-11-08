var IDUsersSelected = [];

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
        console.log("PAS DE SELECTION");

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
        /*
        $(document).on('click', '#deactivateUsers', function() {
            // NOTHING
        });
        $(document).on('click', '#deleteUsers', function() {
            // NOTHING
        });*/
    }
    else if (IDUsersSelected.length > 0)
    {
        // foreach element in IDUsersSelected if the field "Actif ?" is "Non" then disable the "deactivateUsers" button
        var nonActif = false;
        $.each(IDUsersSelected, function(index, value) {
            if ($('#' + value).closest('tr').find('td:eq(7)').text().includes('Non')) {
                console.log(value + " is non actif");
                nonActif = true;
            }
        });
        console.log(nonActif);
        if (nonActif) {
            console.log("BUTTON DESACTIVER INACTIF");
            if ($('#deactivateUsers').hasClass('btn-secondary')) {
                // RESTE INACTIF
            }
            else if ($('#deactivateUsers').hasClass('btn-warning'))
            {
                handleStyleButton('deactivateUsers');
            }
            $(document).off('click', '#deactivateUsers');
            /*$(document).on('click', '#deactivateUsers', function() {
                // NOTHING
            });*/
        }
        else
        {

            console.log("BUTTON DESACTIVER ACTIF");
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
        }
        else if ($('#deactivateUsers').hasClass('btn-warning')) {
            $('#deactivateUsers').removeClass('btn-warning');
            $('#deactivateUsers').addClass('btn-secondary');
        }
    }
    else if (typeBTN === 'deleteUsers') {
        if ($('#deleteUsers').hasClass('btn-secondary')) {
            $('#deleteUsers').removeClass('btn-secondary');
            $('#deleteUsers').addClass('btn-danger');
        }
        else if ($('#deleteUsers').hasClass('btn-danger')) {
            $('#deleteUsers').removeClass('btn-danger');
            $('#deleteUsers').addClass('btn-secondary');
        }
    }
}

function deleteUsers()
{
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

}

function desactivationUser()
{
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
}

$(document).on('click', '#deactivateUsers', function() {
    // NOTHING
});

// When the button with id "deleteUsers" is clicked, make an ajax call foreach id in the array to delete the user
$(document).on('click', '#deleteUsers', function() {
    // NOTHING
});

