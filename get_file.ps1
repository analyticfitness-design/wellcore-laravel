$r = Invoke-RestMethod 'https://api.github.com/repos/analyticfitness-design/wellcore-web/contents/Dockerfile'
[System.Text.Encoding]::UTF8.GetString([Convert]::FromBase64String($r.content.Replace("`n", "")))
