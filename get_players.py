import psutil
import pyautogui
import sys
import time
import requests
import uuid
import json
import random
import platform
from PIL import Image, ImageEnhance, ImageOps, ImageFilter

status = []
status.append({})
status[0]['code'] = 000
status[0]['message'] = ""
status[0]['version'] = "Python " + platform.python_version()

forbidden = [
    "ting",
    "workServer",
    "nting",
    "rterGui",
    "rterPack",
    "IgeService",
    "angeHistoryService",
    "at",
    "ectionService",
    "ntentFilter",
    "eGui",
    "eredSelection",
    "at",
    "okiesService",
    "predSelection",
    "tentFilter",
    "StandService",
    "enBalloon",
    "geService",
    "ngeHistoryService",
    "t",
    "ection Service",
    "kiesService",
    "atpied",
    "terPack",
    "ingeHistoryService",
    "}",
    "-kServer",
    "Gui",
    "Pack",
    "Service",
    "HistoryService",
    "onService",
    "Ji",
    "tFilter",
    "Selection",
    "dSelection",
    "\u041f",
    "n",
    "Lighting",
    "NetworkServer",
    "Starter Gui",
    "StarterPack",
    "BadgeService",
    "ChangeHistoryService",
    "Chat",
    "CollectionService",
    "ContentFilter",
    "CookiesService",
    "CoreGui",
    "FilteredSelection",
    "| Starter Gui",
    "| CoreGui",
    "terGui",
    "C"
]

def get_pid_by_port(port):
    for proc in psutil.process_iter(['pid', 'name']):
        if proc.info['name'] == 'RobloxApp_server.exe':
            try:
                for conn in proc.net_connections(kind='inet'):
                    if conn.laddr.port == port:
                        return proc.info['pid']
            except psutil.AccessDenied:
                continue
    return None

def enhance_image(image):
    enhancer = ImageEnhance.Contrast(image)
    enhanced_image = enhancer.enhance(4)
    enhanced_image = enhanced_image.filter(ImageFilter.SHARPEN)
    return enhanced_image

def get_player_count(port):
    pid = get_pid_by_port(port)
    if pid is not None:
        pyautogui.hotkey('alt', 'tab')

        time.sleep(0.75)

        pyautogui.click(726, 188)

        time.sleep(0.75)

        pyautogui.click(684, 189)
        time.sleep(0.75)

        screenshot = pyautogui.screenshot()
        id = str(uuid.uuid4())
        
        pyautogui.click(684, 189)
        
        api_keys = [
            "api keys here"
        ]

        screenshot_path = './players/' + id + '.png'
        screenshot = screenshot.crop((733, 197, 898, 395))
        screenshot = screenshot.resize((screenshot.width * 32, screenshot.height * 32))
        screenshot = enhance_image(screenshot)
        screenshot = screenshot.convert('L')
        screenshot = ImageOps.colorize(screenshot, (0, 0, 0), (255, 255, 255))
        screenshot.save(screenshot_path)

        image_url = 'https://api.novetusserverlist.com/players/' + id + '.png'
        status[0]['image'] = image_url

        api_url = f'https://api.apilayer.com/image_to_text/url?url={image_url}'
        headers = {'apikey': random.choice(api_keys)}
        response = requests.get(api_url, headers=headers)

        if response.status_code == 200:
            status[0]['message'] = []
            status[0]['code'] = 200
            data = response.json()
            all_text = data.get('all_text', '')
            lines = all_text.split('\n')
            
            for line in lines:
                if not (list(filter(lambda x: x == line, forbidden))):
                    status[0]['message'].append(line)
            return status
        else:
            status[0]['message'] = "Error: " + response.text
            status[0]['code'] = response.status_code
            return status
    else:
        status[0]['message'] = "Process not found"
        status[0]['code'] = 403
        return status

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python script.py <port>")
        sys.exit(1)

    port = int(sys.argv[1])
    player_count = get_player_count(port)

    print(json.dumps(player_count))
