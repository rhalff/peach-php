{form.outputHeader():h}
{form.hidden:h}

<table class="formTable">
    <tr>
        <td width="50%" valign="top"><!-- Personal info -->
            <table width="100%" cellpadding="4">
                <tr>
                    <td class="label">{form.subject.label:h}</td>
                    <td class="element">{form.subject.html:h}</td>
                </tr>
                <tr>
                    <td class="label">{form.summary.label:h}</td>
                    <td class="element">{form.summary.html:h}</td>
                </tr>
                <tr>
                    <td class="label">{form.body.label:h}</td>
                    <td class="element">{form.body.html:h}</td>
                </tr>
                <tr>
                    <td class="label">{form.image_id.label:h}</td>
                    <td class="element">{form.image_id.html:h}</td>
                </tr>
            </table>
        </td>
        
    </tr>
</table>

<table width="600" align="center">
    <tr>
        <td>{form.requirednote:h}</td>
        <td align="right">{form.reset.html:h} {form.submit.html:h}</td>
    </tr>
</table>

</form>

<br />
<b>Collected Errors:</b><br />
{foreach:form.errors,error}
    <font color="red">{error}</font> in element [{name}]<br />
{end:}
