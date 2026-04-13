<?php

namespace App\Core;

/**
 * Código inicial (hello world) para bots de tipo framework.
 * Estos bots usan imágenes base (python:3.11-slim, node:20-alpine, golang:1.22-alpine)
 * sin código propio, por lo que el contenedor se reiniciaría continuamente sin esto.
 */
class StarterCode
{
    /**
     * Devuelve el código inicial para una plantilla por su slug.
     * Retorna null si la plantilla no necesita código inicial.
     */
    public static function get(string $slug): ?string
    {
        return self::CODES[$slug] ?? null;
    }

    /**
     * Códigos iniciales indexados por slug de plantilla.
     */
    private const CODES = [

        // ── Python Telegram Bot ──────────────────────────────────
        'python-telegram-bot' => '#!/usr/bin/env python3
"""Bot de ejemplo — python-telegram-bot. Edita este archivo desde el Gestor de Archivos."""
import os
import logging
from telegram import Update
from telegram.ext import Application, CommandHandler, MessageHandler, filters, ContextTypes

logging.basicConfig(level=logging.INFO)

async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text(
        "¡Hola! Soy tu bot de Telegram.\\n"
        "Edítame desde el Gestor de Archivos en Lanzabot."
    )

async def echo(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text(f"Dijiste: {update.message.text}")

app = Application.builder().token(os.environ["TELEGRAM_BOT_TOKEN"]).build()
app.add_handler(CommandHandler("start", start))
app.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, echo))

print("✅ Bot iniciado correctamente")
app.run_polling()
',

        // ── grammY (Node.js) ─────────────────────────────────────
        'grammy' => '// Bot de ejemplo — grammY. Edita este archivo desde el Gestor de Archivos.
const { Bot } = require("grammy");

const bot = new Bot(process.env.TELEGRAM_BOT_TOKEN);

bot.command("start", (ctx) =>
  ctx.reply("¡Hola! Soy tu bot de Telegram.\\nEdítame desde el Gestor de Archivos en Lanzabot.")
);

bot.on("message:text", (ctx) =>
  ctx.reply(`Dijiste: ${ctx.message.text}`)
);

bot.start();
console.log("✅ Bot iniciado correctamente");
',

        // ── Telegraf (Node.js) ───────────────────────────────────
        'telegraf' => '// Bot de ejemplo — Telegraf. Edita este archivo desde el Gestor de Archivos.
const { Telegraf } = require("telegraf");

const bot = new Telegraf(process.env.TELEGRAM_BOT_TOKEN);

bot.start((ctx) =>
  ctx.reply("¡Hola! Soy tu bot de Telegram.\\nEdítame desde el Gestor de Archivos en Lanzabot.")
);

bot.on("text", (ctx) =>
  ctx.reply(`Dijiste: ${ctx.message.text}`)
);

bot.launch();
console.log("✅ Bot iniciado correctamente");

process.once("SIGINT", () => bot.stop("SIGINT"));
process.once("SIGTERM", () => bot.stop("SIGTERM"));
',

        // ── matrix-nio (Python) ──────────────────────────────────
        'matrix-nio' => '#!/usr/bin/env python3
"""Bot de ejemplo — matrix-nio. Edita este archivo desde el Gestor de Archivos."""
import os
import asyncio
from nio import AsyncClient, RoomMessageText

client = AsyncClient(os.environ["MATRIX_HOMESERVER"], os.environ["MATRIX_USER_ID"])

async def message_cb(room, event):
    if event.sender == os.environ["MATRIX_USER_ID"]:
        return
    if event.body.startswith("!hola"):
        await client.room_send(
            room.room_id, "m.room.message",
            {"msgtype": "m.text", "body": "¡Hola! Soy tu bot Matrix. Edítame desde Lanzabot."}
        )

async def main():
    await client.login(os.environ["MATRIX_PASSWORD"])
    client.add_event_callback(message_cb, RoomMessageText)
    print("✅ Bot Matrix iniciado correctamente")
    await client.sync_forever(timeout=30000)

asyncio.run(main())
',

        // ── whatsapp-web.js (Node.js) ────────────────────────────
        'wwebjs' => '// Bot de ejemplo — whatsapp-web.js. Edita este archivo desde el Gestor de Archivos.
const { Client, LocalAuth } = require("whatsapp-web.js");

const client = new Client({
  authStrategy: new LocalAuth({ dataPath: "/data/wwebjs" }),
  puppeteer: {
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  }
});

client.on("qr", (qr) => {
  console.log("📱 Escanea este QR con tu teléfono:");
  console.log(qr);
});

client.on("ready", () => {
  console.log("✅ ¡WhatsApp bot conectado!");
});

client.on("message", async (msg) => {
  if (msg.body === "!hola") {
    await msg.reply("¡Hola! Soy tu bot de WhatsApp. Edítame desde Lanzabot.");
  }
});

client.initialize();
console.log("Iniciando bot de WhatsApp...");
',

        // ── Telebot Go ───────────────────────────────────────────
        'telebot-go' => 'package main

import (
	"fmt"
	"os"
	"time"

	tele "gopkg.in/telebot.v3"
)

func main() {
	bot, err := tele.NewBot(tele.Settings{
		Token:  os.Getenv("TELEGRAM_BOT_TOKEN"),
		Poller: &tele.LongPoller{Timeout: 10 * time.Second},
	})
	if err != nil {
		fmt.Println("Error:", err)
		return
	}

	bot.Handle("/start", func(c tele.Context) error {
		return c.Send("¡Hola! Soy tu bot en Go. Edítame desde Lanzabot.")
	})

	bot.Handle(tele.OnText, func(c tele.Context) error {
		return c.Send("Dijiste: " + c.Text())
	})

	fmt.Println("✅ Bot iniciado correctamente")
	bot.Start()
}
',

        // ── AmputatorBot (Python / Reddit) ───────────────────────
        'amputatorbot' => '#!/usr/bin/env python3
"""Bot de ejemplo — Reddit (PRAW). Edita este archivo desde el Gestor de Archivos."""
import os
import time
import praw

reddit = praw.Reddit(
    client_id=os.environ.get("REDDIT_CLIENT_ID", ""),
    client_secret=os.environ.get("REDDIT_CLIENT_SECRET", ""),
    username=os.environ.get("REDDIT_USERNAME", ""),
    password=os.environ.get("REDDIT_PASSWORD", ""),
    user_agent="lanzabot:amputatorbot:v1.0"
)

try:
    me = reddit.user.me()
    print(f"✅ Conectado como: {me}")
except Exception as e:
    print(f"⚠️  No se pudo autenticar en Reddit: {e}")
    print("Configura las credenciales en Variables de Entorno")

print("Bot activo. Edita este archivo para añadir tu lógica.")

while True:
    time.sleep(60)
',

        // ── CodeCov Bot (Node.js) ────────────────────────────────
        'codecov-bot' => '// Bot de ejemplo — CodeCov. Edita este archivo desde el Gestor de Archivos.
console.log("✅ CodeCov Bot iniciado correctamente");
console.log("Token CodeCov:", process.env.CODECOV_TOKEN ? "Configurado" : "No configurado");
console.log("Token GitHub:", process.env.GITHUB_TOKEN ? "Configurado" : "No configurado");

console.log("\\nEdita este archivo desde el Gestor de Archivos para añadir tu lógica.");

setInterval(() => {
  console.log(`[${new Date().toISOString()}] Bot activo`);
}, 60000);
',

    ];
}
