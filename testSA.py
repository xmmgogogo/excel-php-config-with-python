#!coding:utf-8
import urllib2

# "MagicSkillService|addMagicSkill|100003698422751"
# "UnlockService|setAllUnlockBuilds|100003698422751"
# "UserHeroService|addHero|100003698422751,180003"
# "PVPService|sendArenaReward"

actionName = "UnlockService|setAllUnlockBuilds|100007237535133"
actionName = actionName.split('|')

# url = "http://dev-fb-td.shinezoneapp.com:1078/dev_branch/v20130228/j7/j7.php?/test/testSA&act=test&s="+actionName[0]+"&c="+actionName[1]+"&p="+actionName[2];
# url = "http://towerdefense-fb.shinezone.com/td/V2013111901/j7/j7.php?/test/testSA&act=test&s="+actionName[0]+"&c="+actionName[1]+"&p="+actionName[2];

print url

res = urllib2.urlopen(url)
print res.read()