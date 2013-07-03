        {if:logoutMessage}
        <p id="loginMessage"><em>{logoutMessage}</em></p>
        {end:}

  <div id="login">
  <form method="post" action="{formaction}" flexy:ignoreonly="yes">

  <fieldset>
    <legend>{login}</legend>

    <p>
    <label for="login" title="Login">{login}</label>
    <input type="text" id="username" name="username" value="" flexy:ignoreonly="yes"/>
    </p>

    <p>
    <label for="password" title="Password">{password}</label>
    <input type="password" id="password" name="password" value="" flexy:ignoreonly="yes"/>
    </p>
    <p>
    <input name="submit" type="submit" class="submit" value="Submit" onClick="this.form.submit(); this.disabled = true; this.value = 'Even geduld a.u.b.';" flexy:ignoreonly="yes"/>
    </p>

  </fieldset>
  </form>
  </div>
