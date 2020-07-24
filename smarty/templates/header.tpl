<!DOCTYPE html>
<html lang="en-us" dir="ltr">
<head>
    <title>{if $title}DOCKontrol | {$title}{else}DOCKontrol{/if}</title>

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.4.6/dist/css/uikit.min.css" />

    <!-- UIkit JS -->
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.4.6/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.4.6/dist/js/uikit-icons.min.js"></script>
    <script
            src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
            crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <style type="text/css">

        @-webkit-keyframes scaleAnimation {
            0% {
                opacity: 0;
                -webkit-transform: scale(1.5);
                transform: scale(1.5);
            }
            100% {
                opacity: 1;
                -webkit-transform: scale(1);
                transform: scale(1);
            }
        }

        @keyframes scaleAnimation {
            0% {
                opacity: 0;
                -webkit-transform: scale(1.5);
                transform: scale(1.5);
            }
            100% {
                opacity: 1;
                -webkit-transform: scale(1);
                transform: scale(1);
            }
        }
        @-webkit-keyframes drawCircle {
            0% {
                stroke-dashoffset: 151px;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }
        @keyframes drawCircle {
            0% {
                stroke-dashoffset: 151px;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }
        @-webkit-keyframes drawCheck {
            0% {
                stroke-dashoffset: 36px;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }
        @keyframes drawCheck {
            0% {
                stroke-dashoffset: 36px;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }
        @-webkit-keyframes fadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
        }
        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
        }
        @-webkit-keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
        #successAnimationCircle {
            stroke-dasharray: 151px 151px;
            stroke: #fff;
        }

        #successAnimationCheck {
            stroke-dasharray: 36px 36px;
            stroke: #fff;
        }

        #successAnimationResult {
            fill: #fff;
            opacity: 0;
        }

        #successAnimation.animated {
            -webkit-animation: 1s ease-out 0s 1 both scaleAnimation;
            animation: 1s ease-out 0s 1 both scaleAnimation;
        }
        #successAnimation.animated #successAnimationCircle {
            -webkit-animation: 1s cubic-bezier(0.77, 0, 0.175, 1) 0s 1 both drawCircle, 0.3s linear 0.9s 1 both fadeOut;
            animation: 1s cubic-bezier(0.77, 0, 0.175, 1) 0s 1 both drawCircle, 0.3s linear 0.9s 1 both fadeOut;
        }
        #successAnimation.animated #successAnimationCheck {
            -webkit-animation: 1s cubic-bezier(0.77, 0, 0.175, 1) 0s 1 both drawCheck, 0.3s linear 0.9s 1 both fadeOut;
            animation: 1s cubic-bezier(0.77, 0, 0.175, 1) 0s 1 both drawCheck, 0.3s linear 0.9s 1 both fadeOut;
        }
        #successAnimation.animated #successAnimationResult {
            -webkit-animation: 0.3s linear 0.9s both fadeIn;
            animation: 0.3s linear 0.9s both fadeIn;
        }


        .ui-error-circle {
            stroke-dasharray: 260.75219025px, 260.75219025px;
            stroke-dashoffset: 260.75219025px;
            animation: ani-error-circle 1.2s linear;
        }
        .ui-error-line1 {
            stroke-dasharray: 54px 55px;
            stroke-dashoffset: 55px;
            stroke-linecap: round;
            animation: ani-error-line 0.15s 1.2s linear both;
        }
        .ui-error-line2 {
            stroke-dasharray: 54px 55px;
            stroke-dashoffset: 55px;
            stroke-linecap: round;
            animation: ani-error-line 0.2s 0.9s linear both;
        }
        @keyframes ani-error-line {
            to {
                stroke-dashoffset: 0;
            }
        }
        @keyframes ani-error-circle {
            0% {
                stroke-dasharray: 0, 260.75219025px;
                stroke-dashoffset: 0;
            }
            35% {
                stroke-dasharray: 120px, 120px;
                stroke-dashoffset: -120px;
            }
            70% {
                stroke-dasharray: 0, 260.75219025px;
                stroke-dashoffset: -260.75219025px;
            }
            100% {
                stroke-dasharray: 260.75219025px, 0;
                stroke-dashoffset: -260.75219025px;
            }
        }


        #replay {
            background: rgba(255, 255, 255, 0.2);
            border: 0;
            border-radius: 3px;
            bottom: 100px;
            color: #fff;
            left: 50%;
            outline: 0;
            padding: 10px 30px;
            position: absolute;
            -webkit-transform: translateX(-50%);
            transform: translateX(-50%);
        }
        #replay:active {
            background: rgba(255, 255, 255, 0.1);
        }

        button.clickable,button.garage_gate_modal{
            transition: background-color 0.8s ease;
        }

        button.clickable.active,button.garage_gate_modal.active{
            background-color: #fffb00;
        }

        button.clickable.success,button.garage_gate_modal.success{
            background-color: #70c22d;
        }

        button.clickable.error,button.garage_gate_modal.error{
            background-color: #ffffff;
        }

        .uk-modal-title {
            font-size: 1.4rem;
        }

        button.garage_gate_modal.success,button.garage_gate_modal.error {
            padding: 0;
        }

        button.garage_gate_modal{
            padding-left: 20%;
            padding-right: 0;
        }

        button.garage_gate_modal div.single_open{
            width: 75%; float: left;
        }

        button.garage_gate_modal div.camera{
            {if !$user.has_camera_access}display:none;{/if}
            width: 25%; float: left;
            background-color: aliceblue;
        }

        button.garage_gate_modal.active div.camera {
            background: none;
        }

        button.spinner{
            padding: 0;
        }


        button.uk-button,h1,h2,h3,h4,h5,.no-select{
            user-select: none; /* supported by Chrome and Opera */
            -webkit-user-select: none; /* Safari */
            -moz-user-select: none; /* Firefox */
            -ms-user-select: none; /* Internet Explorer/Edge */
        }

        div.picture_container{
            position: relative;
            cursor: pointer;
        }

        div.paused_container{
            position: absolute;
            top:calc(50% - 40px);
            right:calc(50% - 40px);
        }

        img.open_garage_gate_modal_camera_picture{
            width: 100%;
            height: 100%;

        }

    </style>


</head>
<body>
