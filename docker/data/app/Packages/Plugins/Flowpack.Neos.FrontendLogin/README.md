Flowpack.Neos.FrontendLogin
===========================

Neos plugin demonstrating a simple "frontend login"

DISCLAIMER:
-----------

This package mainly serves for demonstration purpose. You should be fine using it in productive applications, but if you
need any custom behavior/style it's probably the easiest to create your own login form plugin. It's just a few files.

How-To:
-------

* Install the package to ``Packages/Plugin/Flowpack.Neos.FrontendLogin`` (e.g. via ``composer require flowpack/neos-frontendlogin:~3.0``)
* Login to the Neos backend and create a new page "Login" (e.g. at ``/login``)
* On that page insert the new plugin ``Frontend login form``
* (Optionally) create a page (and subpages) for a "Members area" (e.g. at ``/members``) and protect it as documented below
* Publish all changes
* Create a new Frontend User (you can use the ``neos.neos:user:create`` command, e.g. ``./flow user:create --authentication-provider "Flowpack.Neos.FrontendLogin:Frontend" --roles "Flowpack.Neos.FrontendLogin:User"``)

Now you should be able to test the frontend login by navigating to ``/login.html``

Protected Member Area
---------------------

If you want to create a "Member Area" that is only visible to authenticated frontend users, add the following ``Policy.yaml`` to your site package:

```yaml
privilegeTargets:

  'Neos\ContentRepository\Security\Authorization\Privilege\Node\ReadNodePrivilege':

    'Acme.YourPackage:MembersArea':
        # Replace <NodeIdentifier> with the node's identifier to be targeted (you can see the identifier in the "Additional info" group in the Property Inspector of the Neos Backend)
      matcher: 'isDescendantNodeOf("<NodeIdentifier>")'


roles:

  'Flowpack.Neos.FrontendLogin:User':
    privileges:
      -
          # Grant "frontend users" access to the "Member area"
        privilegeTarget: 'Acme.YourPackage:MembersArea'
        permission: GRANT


  'Neos.Neos:Editor':
    privileges:
      -
          # Grant "backend users" to access the "Member area" - Otherwise those pages would be hidden in the backend, too!
        privilegeTarget: 'Acme.YourPackage:MembersArea'
        permission: GRANT
```

The specified node and all its child-nodes will be hidden from anonymous users!

> **Note:** Replace "Acme.YourPackage" with the package key of your site package and replace "&lt;NodeIdentifier&gt;" with
> the node identifier of the "member area" node (as described).

Rewriting the template path to your package:
--------------------------------------------

You might want to modify the template(s) according to your needs. Create a ``Views.yaml`` file and
add the following configuration there:

```yaml
-
  requestFilter: 'isPackage("Flowpack.Neos.FrontendLogin") && isController("Authentication") && isAction("index")'
  options:
    templatePathAndFilename: 'resource://Acme.YourPackage/Private/Templates/Authenticate/Index.html'
```

Adjust the actual value in ``templatePathAndFilename`` to your needs and copy the [original template](Resources/Private/Templates/Authentication/Index.html) to that location in order to adjust it at will.

Redirect after login/logout:
----------------------------

Since version 2.1 it's possible to specify pages the user will be redirected to after login and/or logout.

*Hint:* In order to redirect to an external URL you can create a Shortcut node pointing to that URL and specify it as target for the* redirect options.
