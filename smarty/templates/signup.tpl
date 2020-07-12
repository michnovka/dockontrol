{include file="header.tpl" title="Sign up"}


<div class="uk-container uk-container-large uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Create new account</span></h2>

    {if $error_message}
        <div class="uk-alert-danger uk-text-center uk-margin-medium" uk-alert>
            <p>{$error_message}</p>
        </div>
    {/if}

    <form class="uk-form-stacked uk-margin-large uk-text-left" method="post">

        <input type="hidden" name="id" value="{$edit_user.id}">

        <div class="uk-margin">
            <label class="uk-form-label" for="username">Username</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.username}uk-form-danger{/if}" id="username" type="text" placeholder="Username" value="{$edit_user.username}" name="username">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="password">Password</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.password}uk-form-danger{/if}" id="password" type="password" placeholder="Enter password" value="" name="password">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="password2">Repeat password</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.password2}uk-form-danger{/if}" id="password2" type="password" placeholder="Re-type password" value="" name="password2">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="name">Name</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.name}uk-form-danger{/if}" id="name" type="text" placeholder="Name" value="{$edit_user.name}" name="name">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="email">Email</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.email}uk-form-danger{/if}" id="email" type="email" placeholder="E-mail" value="{$edit_user.email}" name="email">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="phone">Phone</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.phone}uk-form-danger{/if}" id="phone" type="tel" placeholder="Phone" value="{$edit_user.phone}" name="phone">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="apartment">Apartment</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.apartment}uk-form-danger{/if}" id="apartment" type="text" placeholder="ZX.BY.NNN" value="{$edit_user.apartment}" name="apartment">
            </div>
        </div>


        <div class="uk-margin">
            <div class="uk-form-controls">
                <button type="submit" name="action" value="save" class="uk-button uk-button-primary uk-button-large uk-width-full">Save</button>
            </div>
        </div>
    </form>



</div>


{include file="footer.tpl"}
