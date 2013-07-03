{if:form.javascript} 
<script language="javascript"> 
<!--
    {form.javascript} 
-->
</script> 
{end:} 

<table border="0" class="maintable" align="center"> 
    {form.outputHeader():h}
    {form.hidden:h} 
     
    {foreach:form.sections,sec} 
        <tr> 
            <td class="header" colspan="2"> 
            <b>{sec.header}</b></td> 
        </tr> 
               
        {foreach:sec.elements,elem} 
            {if:elem.style} 
              {elem.outputStyle():h}
            {else:}
        		{if:elem.isButton()} 
                    {if:elem.notFrozen()} 
                    <tr>    
                        <td></td> 
                        <td align="left">{elem.html:h}</td> 
                    </tr> 
                    {end:} 
                {else:} 
                    <tr> 
                    {if:elem.isType(#textarea#)}                
                        <td colspan="2"> 
                            {if:elem.required}<font color="red">*</font>{end:}<b>{elem.label:h}</b><br /> 
                    {else:} 
                        <td align="right" valign="top"> 
                            {if:elem.required}<font color="red">*</font>{end:}<b>{elem.label:h}:</b></td> 
                        <td> 
                    {end:}  
                    {if:elem.error}<font color="red">{elem.error}</font><br />{end:} 
                    {if:elem.isType(#group#)} 
                        {foreach:elem.elements,gitem} 
                            {gitem.label:h} 
                            {gitem.html:h}{if:gitem.required}<font color="red">*</font>{end:}
                            {if:elem.separator}{elem.separator:h}{end:}
                        {end:} 
                    {else:} 
                        {elem.html:h} 
                    {end:} 
                    </td> 
                    </tr> 
                {end:} 
            {end:}    
        {end:} 
     {end:}
        {if:form.requirednote}
        <tr> 
            <td></td> 
            <td valign="top">{form.requirednote:h}</td> 
        </tr> 
        {end:} 
</form>
     
</table> 
