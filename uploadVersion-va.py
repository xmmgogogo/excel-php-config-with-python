#-*- coding:utf-8 -*-
import os
import sys
import re
import os.path
import shutil
import json

# 设置文本编码
reload(sys)
sys.setdefaultencoding('utf-8')

def copytree(src, dst, symlinks=False):
    names = os.listdir(src)
    if not os.path.isdir(dst):
        os.makedirs(dst)
        
    errors = []
    for name in names:
        srcname = os.path.join(src, name)
        dstname = os.path.join(dst, name)
        print srcname
        try:
            if symlinks and os.path.islink(srcname):
                linkto = os.readlink(srcname)
                os.symlink(linkto, dstname)
            elif os.path.isdir(srcname):
                # if re.search(r'configs\\facebook', srcname) or re.search(r'configs\\armorGame', srcname) or re.search(r'configs\\friendSter', srcname) or re.search(r'configs\\kongregate', srcname):
                #     print srcname
                #     continue
                copytree(srcname, dstname, symlinks)
            else:
                if os.path.isdir(dstname):
                    os.rmdir(dstname)
                elif os.path.isfile(dstname):
                    os.remove(dstname)
                shutil.copy2(srcname, dstname)
        except (IOError, os.error) as why:
            errors.append((srcname, dstname, str(why)))
        except OSError as err:
            errors.extend(err.args[0])
    try:
        shutil.copystat(src, dst)
    except WindowsError:
        # can't copy file access times on Windows
        pass
    except OSError as why:
        errors.extend((src, dst, str(why)))

if __name__ == '__main__':
    fileConfig = json.load((file('d:\uploadVersion-va.json')))

    # 导出目录
    sourcePath = fileConfig['sourcePath']

    # 比较目录
    comparePath = fileConfig['comparePath']

    # 上传目录
    toPath_list = fileConfig['toPath_list']
    toPath_keys = fileConfig['toPath_keys']

    # 传入参数
    if(len(sys.argv) > 1) :
        act = sys.argv[1]
    else :
        print u"参数错误！请输入-help"
        sys.exit()

    # 导出    
    if(act == '-d') :
        svncomand = 'TortoiseProc /command:log /path:"' + comparePath + '" /findtype:0'
        os.system(svncomand)
    # 清空缓存    
    if(act == '-f') :
        mulu = sourcePath.split('\\')
        delMulu = mulu[0] + "\\" + mulu[1]
        # print delMulu

        if os.path.isdir(delMulu) :
            shutil.rmtree(delMulu)
            print u"清空导出目录完成>>>" + delMulu
        else :
            print u"导出目录已清空！"            
    # 上传
    elif(act == '-c') :
        toPath_keys = toPath_keys.split(',')
        # print toPath_keys

        f = open("d:\\versioncache-va.log") # 返回一个文件对象
        versionList = {}
        num = 0

        line = f.readline()             # 调用文件的 readline()方法
        while line:
            # print line,                 # 后面跟 ',' 将忽略换行符
            key = toPath_keys[num]
            versionList[key] = line.strip('\n')
            line = f.readline()
            num += 1
        f.close()

        # 拼合路径
        path_list = toPath_list.split(',')
        for plantFormId in path_list:
            svnPath = fileConfig['toPath_' + plantFormId]
            svnPath = svnPath + versionList[plantFormId]
            print svnPath

            # 拷贝并提交
            if os.path.isdir(svnPath) :
                copytree(sourcePath, svnPath)
                print u"Upload To Directory>>>>>> " + plantFormId
            
                svncomand = 'TortoiseProc /command:commit /path:"' + svnPath + '"'
                os.system(svncomand)
            else :
               print u"版本路径不存在" + ">>>>>> " + svnPath
               break
    # 更新版本
    elif(act == '-u') :
        if(len(sys.argv) == 3) :
                plantFormId = sys.argv[2]
                svnPath = fileConfig['toPath_' + plantFormId]
                oldPath = svnPath #os.path.split(os.path.realpath(svnPath))[0]
                os.system('TortoiseProc /command:update /path:"' + oldPath + '"' + '"')
        else :
            print u"参数错误！请输入平台id"
            sys.exit()
    # 打tag
    elif(act == '-t') :
        if(len(sys.argv) < 4) :
            if(len(sys.argv) == 3) :
                plantFormId = sys.argv[2]
                svnPath = fileConfig['toPath_' + plantFormId]
                oldPath = svnPath #os.path.split(os.path.realpath(svnPath))[0]
                os.system("dir " + oldPath + "/b")
            else :
                print u"参数错误！请输入平台id from"
                sys.exit()
        else :
            plantFormId = sys.argv[2]
            svnPath = fileConfig['toPath_' + plantFormId]
            # 老版本号
            oldVersionName  = sys.argv[3]

            if(svnPath) :
                oldDir = svnPath #os.path.split(os.path.realpath(svnPath))[0]
                oldPath = oldDir + "\\" + oldVersionName
                if(os.path.isdir(oldPath)) :
                    # os.system('TortoiseProc /command:update /path:"' + oldDir + '"' + '"')
                    os.system('TortoiseProc /command:copy /path:"' + oldPath + '"' + '" /logmsg:"mm auto create tag!"')
                else :
                    print u"tag路径不正确，检查路径>>> " + oldPath
    # 帮助功能
    elif(act == '-h') : 
        print u'''
            使用命令说明：
            -d 导出上传文件
            -c 拷贝文件并提交版本
            -u 更新版本
            -t 打tag 平台id 老版本号(uploadVersion-va.py -t lk V2014112401)
            -h 帮助help
        '''           
    else :
        # toPath_keys = toPath_keys.split(',')
        # # print toPath_keys

        # f = open("c:\\versioncache.log") # 返回一个文件对象
        # versionList = {}
        # num = 0

        # line = f.readline()             # 调用文件的 readline()方法
        # while line:
        #     key = toPath_keys[num]
        #     versionList[key] = line.strip('\n') 
        #     line = f.readline()
        #     num += 1
        # f.close()
        # print versionList

        print u"参数错误！请输入-b,-u"
        sys.exit()