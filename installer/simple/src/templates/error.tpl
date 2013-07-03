<div id="errorMessage">
{if:error_message_prefix}
{error_message_prefix}
{end:}

{if:error_message_mode}
{mode}
{end:}

{if:level}
{level}
{end:}

{if:code}
{code}
{end:}

{if:message}
<p>
{message}
{t.getUserMessage()}
</p>
{end:}
</div>
