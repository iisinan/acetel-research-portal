awk '
/catch \(\\Exception \$e\)/ {
    print "    } catch (\\Exception $e) {"
    print "        throw $e;"
    print "    }"
    skip = 1
    next
}
skip && /\]\, 500\)\;/ {
    skip = 0
    next
}
skip { next }
{ print }
' backend/app/Http/Controllers/DashboardController.php > temp.php && mv temp.php backend/app/Http/Controllers/DashboardController.php
