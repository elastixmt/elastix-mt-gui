function defaultValues(totalRepos,centosversion,arquitectura)
{
    for(var i=0;i<totalRepos;i++){
        var chkbox = document.getElementById("repo-"+i);
        var repo = $("#repo-"+i).parent().next().html();
        if(repo == "CentOS-"+centosversion+" - Base" || repo == "CentOS-"+centosversion+" - Updates" || repo == "CentOS-"+centosversion+" - Addons" || repo == "CentOS-"+centosversion+" - Extras" || repo == "Base RPM Repository for Elastix" || repo == "Updates RPM Repository for Elastix" || repo == "Extras RPM Repository for Elastix" || repo == "Extra Packages for Enterprise Linux 5 - "+arquitectura || repo == "Base RPM Repository for Elastix Commercial-Addons" || repo == "Loway Research Yum Repository" ||
        repo == "Commercial-Addons RPM Repository for Elastix")
            chkbox.checked = true;
        else
            chkbox.checked = false;
    }
}