function sendCommand(command,param,hostId,vmId)
{
    createDiv(command);
    if( param.length !== undefined || param.length <= 0)
    {
       displayParam(command,param,hostId,vmId);
    }
    else
    {
        exec(command,param,hostId,vmId);
    }

}

function displayParam(command,param,hostId,vmId)
{
    var frm = document.createElement('form');
    frm.method = 'post';
    frm.action = 'ajax.php?hostid='+hostId+'&vmid='+vmId+'&driverCommand='+command;
    frm.onSubmit = 'return false;';
    frm.name = 'name_'+command;
    frm.id = 'id_'+command;

    var tab=document.createElement('table');
    frm.appendChild(tab);

    var tbo=document.createElement('tbody');
    tab.appendChild(tbo);
    var row, cell;
        
    for(var i in param)
    {
        row=document.createElement('tr');
        //name
        cellName=document.createElement('td');
        cellName.appendChild(document.createTextNode(param[i].label))
        row.appendChild(cellName);
        
        //element
        cellElement=document.createElement('td');
        var input = document.createElement("input");
        input.name =  param[i].name ;
        if(param[i].type == 'bool')
        {
            input.type = "checkbox";
            input.checked = param[i].default ;
        }else{
            input.type = "text";
            input.value = param[i].default ;
        }
        cellElement.appendChild(input)
        row.appendChild(cellElement);
        
        tbo.appendChild(row);
        
    }
    
    $('#'+command).append(frm);
    $( "#"+command ).dialog({
            buttons: {
                "Execute command": function() {
                    //$( this ).dialog( "close" );
                    //exec(command,param,hostId,vmId);
                    sendFormAjax($('#id_'+command),command);
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                    removeDiv(command);
                }
            },
        });
}

function sendFormAjax(form,command)
{
    var img = document.createElement('img');
    img.setAttribute("src","web/img/load.gif")
    var divTag = document.getElementById(command);
    while(divTag.firstChild){
        divTag.removeChild(divTag.firstChild);
    }
    divTag.appendChild(img);

    $.ajax({
        url: $(form).attr('action'),
        type: $(form).attr('method'),
        data: $(form).serialize(),
        success: function(data, textStatus, jqXHR) {
            $('#'+command).text(data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
             $('#'+command).text("Error ajax request");
        }
    });
    
    $( "#"+command ).dialog({
        close: function( event, ui ) {removeDiv(command);},
        buttons: {}
    });
}

function exec(command,param,hostId,vmId)
{
    var img = document.createElement('img');
    img.setAttribute("src","web/img/load.gif")
    var divTag = document.getElementById(command);
    divTag.appendChild(img);
    
    jQuery.ajax({
        type: 'GET',
        url: 'ajax.php',
        data: {
            actionajax: 'driverCommand',
            driverCommand: command,
            hostid: hostId,
            vmid:vmId,
            
        }, 
        success: function(data, textStatus, jqXHR) {
            $('#'+command).text(data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
             $('#'+command).text("Error ajax request");
        }
    });
    
    $( "#"+command ).dialog({
        close: function( event, ui ) {removeDiv(command);},
        buttons: {}
    });
}

function createDiv(id)
{ 
    var divTag = document.createElement("div"); 
    divTag.id = id; 
    divTag.setAttribute("title", id); 
    document.body.appendChild(divTag); 
}

function removeDiv(id)
{
    $('#'+id).remove();
}

function displayAjaxFunction(el,data)
{
    jQuery.ajax({
        type: 'GET',
        url: 'ajax.php',
        data: data, 
        success: function(data, textStatus, jqXHR) {
            el.innerHTML = data;
        },
        error: function(jqXHR, textStatus, errorThrown) {
             el.innerHTML = "Error ajax request";
        }
    });
}