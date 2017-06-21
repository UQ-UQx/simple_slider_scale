import '../stylesheets/Container.scss'
import '../../node_modules/bootstrap-slider/dist/css/bootstrap-slider.min.css'


import React from "react"

import axios from "axios"
import { Icon } from "react-fa"
import SimpleSlider from "./SimpleSlider"


const minLabelImportantText = "Not important at all"
const maxLabelImportantText = "Very important"

const minLabelWellText = "Not at all well"
const maxLabelWellText = "Very well"

const areaQuestion = "How important is this area of living to you?"
const valueQuestion = "How well are you living your values in this area?"
const structure = [

    {
        id:"question_1",
        title:"Family and friends",
        sliders:[
            {
                name:"slider_1",
                question:areaQuestion,
                min_label:minLabelImportantText,
                max_label:maxLabelImportantText
            },
            {
                name:"slider_2",
                question:valueQuestion,
                min_label:minLabelWellText,
                max_label:maxLabelWellText
            }
        ]
    },
    {
        id:"question_2",
        title:"Work and career",
        sliders:[
            {
                name:"slider_1",
                question:areaQuestion,
                min_label:minLabelImportantText,
                max_label:maxLabelImportantText
            },
            {
                name:"slider_2",
                question:valueQuestion,
                min_label:minLabelWellText,
                max_label:maxLabelWellText
            }
        ]
    },
    {
        id:"question_3",
        title:"Recreation and personal development",
        sliders:[
            {
                name:"slider_1",
                question:areaQuestion,
                min_label:minLabelImportantText,
                max_label:maxLabelImportantText
            },
            {
                name:"slider_2",
                question:valueQuestion,
                min_label:minLabelWellText,
                max_label:maxLabelWellText
            }
        ]
    },
    {
        id:"question_4",
        title:"Spirituality and community",
        sliders:[
            {
                name:"slider_1",
                question:areaQuestion,
                min_label:minLabelImportantText,
                max_label:maxLabelImportantText
            },
            {
                name:"slider_2",
                question:valueQuestion,
                min_label:minLabelWellText,
                max_label:maxLabelWellText
            }
        ]
    },
    {
        id:"question_5",
        title:"Health and physical wellbeing",
        sliders:[
            {
                name:"slider_1",
                question:areaQuestion,
                min_label:minLabelImportantText,
                max_label:maxLabelImportantText
            },
            {
                name:"slider_2",
                question:valueQuestion,
                min_label:minLabelWellText,
                max_label:maxLabelWellText
            }
        ]
    }


]
export default class Container extends React.Component {
    constructor(props){
        super(props);
        this.structure = structure

        let defaultState = {}
        let slider_ids = structure.forEach((question, ind)=>{
            let slider_ids = question.sliders.forEach((slider, sliderIndex)=>{
                defaultState[question.id+"_"+slider.name] = 0;
            })
        })

        defaultState["submitted"] = false;
        defaultState["submitting"] = false;
                
        props.appState ? this.state = props.appState : this.state = defaultState
    
        this.handleSliderStop = this.handleSliderStop.bind(this)
        this.handleSubmitButtonClick = this.handleSubmitButtonClick.bind(this)

    }
    componentWillMount(){
        console.log("Layout component will mount")
        
    }
    componentDidMount(){
        console.log("Layout component did mount");

    }
    componentWillUnmount(){
        console.log("Layout component will unmount")
    }

    handleSliderStop(event){

        console.log(event);

        this.setState({
            [event.name]:event.target.value
        })

    }
    
    handleSubmitButtonClick(event){
        this.setState({
            "submitting":true
        })
        if(!this.state.submitted){
            var app = this;
            const postData = new FormData();
            postData.append('file', this.state.image_file);
            postData.append('action', "setUserState");
            postData.append('app_state', JSON.stringify(this.state));
            postData.append('user_id', $LTI_userID);
            postData.append('lti_id', $LTI_resourceID);
            postData.append('lti_grade_url', $LTI_grade_url);
            postData.append('lti_consumer_key', $LTI_consumer_key);
            postData.append('result_sourcedid', $LTI_result_sourcedid);

            axios.post('../public/api/api.php', postData)
            .then(function(response){
                app.setState({...response.data, "submitting":false});
            }).catch(function(error){
                app.setState({
                    submitting:false
                })
                console.log("Submit Failed ", error.response);
            });
        }
    }

    render(){

        const questions = this.structure.map(question=>{
           const sliders = question.sliders.map(slider=>{

                let slider_name = question.id+"_"+slider.name

                return (<div className="question-slider-container" key={question.id+slider.name}>
                        <h4>{slider.question}</h4>
                        <SimpleSlider
                            name={slider_name}
                            current_value={this.state[slider_name]}
                            min={0}
                            max={100}
                            step={1}
                            show_tooltip={false}
                            min_label={slider.min_label}
                            max_label={slider.max_label}
                            onSlideStop={this.handleSliderStop}
                        />
                </div>)
           })
           return(<div className="question-container clearfix" key={question.id}>
                <h3>{question.title}</h3>
                {sliders}
            </div>)
        })


        let submit_button_text = "Submit"
        let disabled_class = ""
        let disabled_prop = {}
        if(this.state.submitting){
            submit_button_text = "Submitting "+<Icon spin name="spinner"/>;
            disabled_class = "disabled"
            disabled_prop = {'disabled':true}
        }

        if(this.state.submitted){
            submit_button_text = "Submitted"
            disabled_class = "disabled"
            disabled_prop = {'disabled':true}
        }

        let submit_button = (<button className={"btn btn-primary submit-button "+disabled_class} 
                                    onClick={this.handleSubmitButtonClick} {...disabled_prop}>{submit_button_text}</button>)


        return (
        <div className="container-component clearfix">
            {questions}
            {submit_button}
        </div>);
    }
}


