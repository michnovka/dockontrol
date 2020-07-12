{include file="header.tpl" title="Dock CP Login"}
<div class="uk-container uk-container-small uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Dock Z9 Login</span></h2>


    {if $error}
    <div class="uk-alert-danger uk-text-center uk-margin-medium" uk-alert>
        <p>{$error}</p>
    </div>
    {/if}

    {if $logged_out}
    <div class="uk-alert-success uk-text-center uk-margin-medium" uk-alert>
        <p>You have successfully logged out</p>
    </div>
    {/if}


    <form action="login.php" method="post">
        <div class="uk-grid-small uk-text-center uk-grid-row-small" uk-grid>
            <div class="uk-width-1-2@l"><input class="uk-input" type="text" name="username" placeholder="Username" value="{$username}"></div>
            <div class="uk-width-1-2@l"><input class="uk-input" type="password" name="password" placeholder="Password"></div>
            <div class="uk-width-1-1@l"><button name="action" type="submit" class="uk-button uk-button-large uk-button-primary uk-width-1-1 clickable" value="log_in">Log in</button></div>
        </div>
    </form>

</div>
{include file="footer.tpl"}