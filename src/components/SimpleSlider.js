import '../stylesheets/SimpleSlider.scss'

import React from "react"

import axios from "axios"
import PropTypes from "prop-types"
import ReactBootstrapSlider from 'react-bootstrap-slider';


export default class SimpleSlider extends React.Component {
    constructor(props){
        super(props);

        this.state = {
            current_value:props.current_value
        }

        this.handleOnChange = this.handleOnChange.bind(this)
        this.handleOnSlideStop = this.handleOnSlideStop.bind(this)
        this.handleWindowResize = this.handleWindowResize.bind(this)
    }

    componentDidMount(){
        window.addEventListener("resize", this.updateDimensions);
    }

    handleWindowResize(event){

        console.log(event)

    }

    handleOnChange(event){
        this.setState({
            current_value:event.target.value
        })

        let eventToSend = {...event, "name":this.props.name}

        this.props.onChange(eventToSend)
    }

    handleOnSlideStop(event){
        this.setState({
            current_value:event.target.value
        })
        
        let eventToSend = {...event, "name":this.props.name}

        this.props.onSlideStop(eventToSend)
    }

    render(){


        return(<div className="simple-slider-component clearfix">
        
            <div className="slider-component-containers slider-min-label-container">{this.props.min_label}</div>
            <div className="slider-component-containers bootstrap-slider-container">
                <ReactBootstrapSlider
                    value={this.state.current_value}
                    change={this.handleOnChange}
                    slideStop={this.handleOnSlideStop}
                    tooltip={this.props.show_tooltip ? "show":"hide"}
                    step={this.props.step}
                    max={this.props.max}
                    min={this.props.min}
                />
            </div>
            <div className="slider-component-containers slider-max-label-container">{this.props.max_label}</div>

        </div>)
    }

}

SimpleSlider.PropTypes = {

    name: PropTypes.string,
    current_value: PropTypes.number,
    min: PropTypes.number,
    max: PropTypes.number,
    step: PropTypes.number,
    show_tooltip: PropTypes.bool,
    onChange: PropTypes.func,
    onSlideStop: PropTypes.func,
    min_label: PropTypes.string,
    max_label: PropTypes.string
    
};

SimpleSlider.defaultProps = {
  
    name: "default_slider",
    current_value: 50,
    min: 0,
    max: 100,
    step: 1,
    show_tooltip: false,
    min_label: "Label",
    max_label: "Label",
    onChange: event => {
        //console.log("Value Changing: ", event.target.value)
    },
    onSlideStop: event => {
        //console.log("Slide Stopped: ", event.target.value)
    }

}