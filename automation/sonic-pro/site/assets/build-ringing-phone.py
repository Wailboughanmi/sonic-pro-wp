"""Draw a small, self-hosted red ringing phone GIF for email clients."""
from pathlib import Path
from PIL import Image, ImageDraw
import math

OUT=Path(__file__).resolve().parent
SIZE=144
SCALE=4
RED=(200,39,54)

def point(p):return tuple(round(x*SCALE) for x in p)
def curve(a,b,c,d,steps=18):
    return [((1-t)**3*a[0]+3*(1-t)**2*t*b[0]+3*(1-t)*t*t*c[0]+t**3*d[0],
             (1-t)**3*a[1]+3*(1-t)**2*t*b[1]+3*(1-t)*t*t*c[1]+t**3*d[1]) for t in [i/steps for i in range(steps+1)]]

def handset():
    # A rounded receiver with two earpieces, drawn as an original filled vector path.
    p=[(44,37)]
    p+=curve((44,37),(40,34),(34,39),(32,44))
    p+=curve((32,44),(28,62),(55,98),(80,109))
    p+=curve((80,109),(88,113),(98,112),(104,104))
    p+=curve((104,104),(109,98),(109,94),(104,90))
    p+=[(89,80)]
    p+=curve((89,80),(85,78),(81,80),(78,84))
    p+=[(74,89)]
    p+=curve((74,89),(62,82),(53,70),(49,60))
    p+=[(56,56)]
    p+=curve((56,56),(60,53),(61,49),(58,45))
    p+=[(48,35),(44,37)]
    layer=Image.new('RGBA',(SIZE*SCALE,SIZE*SCALE),(0,0,0,0))
    ImageDraw.Draw(layer).polygon([point(q) for q in p],fill=RED+(255,))
    return layer

phone=handset()
frames=[]
# About two seconds per loop, with a short rest between ring bursts.
angles=[0,-9,10,-9,8,-6,5,0,0,0,0,-8,9,-7,6,0,0,0,0,0]
for i,angle in enumerate(angles):
    canvas=Image.new('RGB',(SIZE*SCALE,SIZE*SCALE),'white')
    draw=ImageDraw.Draw(canvas)
    active=i in range(1,7) or i in range(11,15)
    # Ringing marks are visible in the first frame as a useful static fallback.
    if active or i==0:
        width=round((3.2 if active else 2.5)*SCALE)
        draw.arc([29*SCALE,21*SCALE,113*SCALE,103*SCALE],270,355,fill=RED,width=width)
        draw.arc([16*SCALE,9*SCALE,126*SCALE,119*SCALE],270,355,fill=RED,width=width)
    moved=phone.rotate(angle,Image.Resampling.BICUBIC,center=(72*SCALE,72*SCALE))
    canvas.paste(moved,(0,0),moved)
    canvas=canvas.resize((SIZE,SIZE),Image.Resampling.LANCZOS)
    # Keep a common palette for clean animation and a small email payload.
    if not frames:
        palette=canvas.quantize(colors=48)
    frames.append(canvas.quantize(palette=palette))
frames[0].save(OUT/'ringing-phone-red-v1.gif',save_all=True,append_images=frames[1:],duration=100,loop=0,disposal=2,optimize=False)
frames[0].convert('RGB').save(OUT/'ringing-phone-red-first-frame.png')
with Image.open(OUT/'ringing-phone-red-v1.gif') as check:
    assert check.n_frames>1 and check.info.get('loop')==0 and check.size==(SIZE,SIZE)
print('Animated GIF generated:',(OUT/'ringing-phone-red-v1.gif').stat().st_size,'bytes')
