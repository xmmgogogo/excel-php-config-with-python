#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os
import sys
import re
import os.path
import shutil
import json
import wx

class TextFrame(wx.Frame):
    def __init__(self):
        self.fileConfig = json.load((file('c:\uploadVersion22.json')))

        wx.Frame.__init__(self, None, -1, 'KOT Upload Tool', size=(600, 500))
        panel = wx.Panel(self, -1)
        font = wx.Font(20, wx.DECORATIVE, wx.ITALIC, wx.NORMAL)
        
        # FB
        fbLabel = wx.StaticText(panel, -1, "fb:")
        self.fbText = wx.TextCtrl(panel, -1, "D:\\td_release\\td2\\V2014112701\\", size=(275, -1))
        # fbLabel.SetFont(font)
        self.button1 = wx.Button(panel, -1, "update")
        self.Bind(wx.EVT_BUTTON, self.up_fb, self.button1)
        self.button1.SetDefault()

        # AG
        agLabel = wx.StaticText(panel, -1, "ag:")
        agText = wx.TextCtrl(panel, -1, "D:\\release-ag\\V2014112501\\", size=(275, -1))    
        self.button2 = wx.Button(panel, -1, "update")
        self.Bind(wx.EVT_BUTTON, self.up_ag, self.button2)
        self.button2.SetDefault()

        sizer = wx.FlexGridSizer(10, 3, 9, 25)

        sizer.AddMany([fbLabel, self.fbText, self.button1, agLabel, agText, self.button2])
        panel.SetSizer(sizer)

    def up_fb(self, event):
        sourcePath = self.fileConfig['toPath_fb']
        os.system('TortoiseProc /command:update /path:"' + sourcePath + '"' + '"')
        # print self.fbText.GetValue() 

    def up_ag(self, event):
        # self.button.SetLabel("Clicked")
        sourcePath = self.fileConfig['toPath_ag']
        os.system('TortoiseProc /command:update /path:"' + sourcePath + '"' + '"')

    def up_kg(self, event):
        # self.button.SetLabel("Clicked")
        sourcePath = self.fileConfig['toPath_kg']
        os.system('TortoiseProc /command:update /path:"' + sourcePath + '"' + '"')      

if __name__ == '__main__':
    app = wx.PySimpleApp()
    frame = TextFrame()
    frame.Show()
    app.MainLoop()